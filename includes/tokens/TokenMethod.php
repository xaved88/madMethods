<?php
declare(strict_types=1);

namespace MM\tokens;

use Exception;

class TokenMethod extends TokenContainer
{
    public const REPLACEABLE                     = 0;
    public const NO_REPLACE_VISIBILITY           = 1;
    public const NO_REPLACE_CALLS_MEMBER_METHODS = 2;
    public const NO_REPLACE_EARLY_RETURN         = 3;
    public const NO_REPLACE_CONSTRUCTOR          = 4;
    public const CLASS_IS_INTERFACE              = 5;

    public const CODE_TO_STRING = [
        0 => 'Replaced',
        1 => 'Not Replaced: Visibility',
        2 => 'Not Replaced: Calls member methods',
        3 => 'Not Replaced: Early Return',
        4 => 'Not Replaced: Constructor',
        5 => 'NOTHING Replaced: Class Is Interface',
    ];

    private const VISIBILITY_PUBLIC    = 'public';
    private const VISIBILITY_PROTECTED = 'protected';
    private const VISIBILITY_PRIVATE   = 'private';

    public $name;

    private $visibility;

    public function __construct(array $tokens)
    {
        parent::__construct($tokens);
        $this->parseMetadata();
    }

    public function isReplacable(): bool
    {
        if ($this->visibility !== self::VISIBILITY_PRIVATE) {
            return false;
        }

        return !$this->doesCallMemberMethods();
    }

    public function getReplaceableCode(): int
    {
        if ($this->name == "__construct") {
            return static::NO_REPLACE_CONSTRUCTOR;
        }

        if ($this->doesCallMemberMethods()) {
            return static::NO_REPLACE_CALLS_MEMBER_METHODS;
        }

        if ($this->visibility !== self::VISIBILITY_PRIVATE) {
            return static::NO_REPLACE_VISIBILITY;
        }

        if ($this->hasEarlyReturn()) {
            return static::NO_REPLACE_EARLY_RETURN;
        }

        return static::REPLACEABLE;
    }

    public function findUsagesOfMemberMethod(TokenMethod $method): array
    {
        $usages = [];

        $startIndex = 0;
        do {
            list($foundIndex, $methodName) = $this->getNextMemberMethodCall($startIndex);
            if ($foundIndex === -1) {
                break;
            }
            $startIndex = $foundIndex + 1;
            if ($methodName !== $method->name) {
                continue;
            }

            $usages[] = [$foundIndex, $this->findEndIndexOfMethodCall($startIndex), $this];
        } while (true);

        return $usages;
    }

    /**
     * @return Token[]
     */
    public function getContents(): array
    {
        $openBracketIndex = $this->getNextTokenIndexOfTypeAndValue(Token::T_RAW_TEXT, '{');
        $startIndex       = $this->getNextTokenIndexNotOfType(T_WHITESPACE, $openBracketIndex + 1);
        // -2 because of the } closing bracket guaranteed
        $endIndex = $this->getPrevTokenIndexNotOfType(T_WHITESPACE, count($this->tokens) - 2);

        return array_slice($this->tokens, $startIndex, $endIndex - $startIndex + 1);
    }

    /**
     * @return Token[][]
     */
    public function getArguments(): array
    {
        $endIndex          = $closeParenIndex = $this->getNextTokenIndexOfTypeAndValue(Token::T_RAW_TEXT, '{');
        $argumentContainer = TokenMethodArgumentContainer::create($this, 0, $endIndex);

        return $argumentContainer->getArgumentValuesWithoutTypeHints();
    }

    /**
     * @param Token[]   $contents
     * @param Token[][] $arguments
     * @param int       $startIndex
     * @param int       $endIndex
     */
    public function replaceContentsWithoutReturn(
        array $contents,
        array $arguments,
        int $startIndex,
        int $endIndex
    ): void {
        $contentForRename = [$contents];
        $this->renameIdenticalVariables($arguments, $contentForRename);
        $contents   = reset($contentForRename);
        $methodCall = $this->getMethodCallContainer($arguments, $startIndex, $endIndex);
        $this->replaceContents($contents, $startIndex, $endIndex);
        $this->replaceArguments($methodCall, $arguments, $startIndex);
    }

    /**
     * @param Token[]   $preReturnContents
     * @param Token[]   $postReturnContents
     * @param Token[][] $arguments
     * @param int       $startIndex
     * @param int       $endIndex
     */
    public function replaceContentsWithReturn(
        array $preReturnContents,
        array $postReturnContents,
        array $arguments,
        int $startIndex,
        int $endIndex
    ): void {
        // Wrap the result in parenthesis to make sure order of operations stays the same
        $openParenToken  = Token::create('(');
        $closeParenToken = Token::create(')');
        array_unshift($postReturnContents, $openParenToken);
        $postReturnContents[] = $closeParenToken;

        $contentForRename = [$preReturnContents, $postReturnContents];
        $this->renameIdenticalVariables($arguments, $contentForRename);
        $preReturnContents  = $contentForRename[0];
        $postReturnContents = $contentForRename[1];

        $methodCall = $this->getMethodCallContainer($arguments, $startIndex, $endIndex);
        $this->replaceContents($postReturnContents, $startIndex, $endIndex);
        $this->insertSafeCodeInline($preReturnContents, $startIndex);
        $this->replaceArguments($methodCall, $arguments, $startIndex);
    }

    /**
     * @param Token[] $contents
     * @param int     $index
     */
    public function insertContents(array $contents, int $index): void
    {
        $newTokens = array_slice($this->tokens, 0, $index);
        $newTokens = array_merge($newTokens, $contents);
        $newTokens = array_merge($newTokens, array_slice($this->tokens, $index));

        $this->tokens = $newTokens;
    }

    /**
     * @param Token[] $contents
     * @param int     $startIndex
     * @param int     $endIndex
     *
     * @internal param Token[] $arguments
     */
    private function replaceContents(array $contents, int $startIndex, int $endIndex): void
    {
        $newTokens = array_slice($this->tokens, 0, $startIndex);
        $newTokens = array_merge($newTokens, $contents);

        $offset              = 1;
        $lastTokenOfContents = end($contents);
        $firstOfTheNew       = $this->tokens[$endIndex + $offset];
        if ($lastTokenOfContents->id === Token::T_RAW_TEXT && $lastTokenOfContents->text === ';' && $firstOfTheNew->id === Token::T_RAW_TEXT && $firstOfTheNew->text === ';') {
            $offset = 2;
        }
        $newTokens = array_merge($newTokens, array_slice($this->tokens, $endIndex + $offset));

        $this->tokens = $newTokens;
    }

    /**
     * @param Token[] $contents
     * @param int     $startIndex
     */
    private function insertSafeCodeInline(array $contents, int $startIndex): void
    {
        $prevIndex     = max(
            $this->getPrevTokenIndexOfTypeAndValue(Token::T_RAW_TEXT, ';', $startIndex - 1),
            $this->getPrevTokenIndexOfTypeAndValue(Token::T_RAW_TEXT, '{', $startIndex - 1)
        );
        $insertAtIndex = $this->getNextTokenIndexNotOfType(T_WHITESPACE, $prevIndex);

        $this->insertContents($contents, $insertAtIndex + 1);
    }

    /**
     * @param Token[][] $arguments
     * @param int       $startIndex
     * @param int       $endIndex
     *
     * @return TokenMethodArgumentContainer|null
     */
    private function getMethodCallContainer(array $arguments, int $startIndex, int $endIndex)
    {
        if (empty($arguments) || empty(reset($arguments))) {
            return null;
        }

        return TokenMethodArgumentContainer::create($this, $startIndex, $endIndex);
    }

    /**
     * @param TokenMethodArgumentContainer|null $method
     * @param Token[][]                         $arguments
     * @param int                               $startIndex
     *
     * @throws Exception
     */
    private function replaceArguments(
        ?TokenMethodArgumentContainer $method,
        array $arguments,
        int $startIndex
    ): void {
        if (!$method) {
            return;
        }
        $values = $method->getArgumentValues();

        $newStuff  = [];
        $space     = Token::create([T_WHITESPACE, ' ']);
        $equals    = Token::create('=');
        $semicolon = Token::create(';');
        foreach ($arguments as $index => $argument) {
            if (isset($values[$index])) {
                $newStuff[] = $space;
                $newStuff   = array_merge($newStuff, $argument);
                $newStuff   = array_merge(
                    $newStuff,
                    [
                        $space,
                        $equals,
                        $space,
                    ]
                );
                $newStuff   = array_merge($newStuff, $values[$index]);
                $newStuff[] = $semicolon;
                $newStuff[] = $space;
            } else {
                $newStuff[] = $space;
                $newStuff   = array_merge($newStuff, $argument);
                $newStuff[] = $semicolon;
                $newStuff[] = $space;
            }
        }

        $this->insertSafeCodeInline($newStuff, $startIndex);
    }

    private function parseMetadata(): void
    {
        $functionIndex = $this->getNextTokenIndexOfType(T_FUNCTION);
        $methodIndex   = $this->getNextTokenIndexOfType(T_STRING, $functionIndex);
        $this->name    = $this->tokens[$methodIndex]->text;

        $visibilityIndex  = $this->getPrevTokenIndexNotOfType(T_WHITESPACE, $functionIndex - 1);
        $this->visibility = $this->tokens[$visibilityIndex]->text;
    }

    private function doesCallMemberMethods(): bool
    {
        list($index, $methodName) = $this->getNextMemberMethodCall();

        return $index !== -1;
    }

    private function getNextMemberMethodCall(int $startIndex = 0): array
    {
        $methodName = null;
        $tokenCount = count($this->tokens);
        do {
            // I expect to see a $this->method() call
            $thisIndex = $this->getNextTokenIndexOfTypeAndValue(T_VARIABLE, '$this', $startIndex);
            if ($thisIndex === -1) {
                return [-1, null];
            }

            // I expect to see the '->' now, though whitespace may come
            $nextSymbolIndex = $this->getNextTokenIndexNotOfType(T_WHITESPACE, $thisIndex + 1);
            if ($nextSymbolIndex === -1 || $this->tokens[$nextSymbolIndex]->id !== T_OBJECT_OPERATOR) {
                $startIndex = $nextSymbolIndex + 1;
                continue;
            }

            // I expect to see a string for the method name now, though whitespace may come
            $stringIndex = $this->getNextTokenIndexNotOfType(T_WHITESPACE, $nextSymbolIndex + 1);
            if ($stringIndex === -1 || $this->tokens[$stringIndex]->id !== T_STRING) {
                $startIndex = $stringIndex + 1;
                continue;
            }

            // I expect the next non whitespace to be an open parenthesis, otherwise it's just a member var being accessed
            $nextIndex = $this->getNextTokenIndexNotOfType(T_WHITESPACE, $stringIndex + 1);
            $nextOpen  = $this->getNextTokenIndexOfTypeAndValue(Token::T_RAW_TEXT, '(', $stringIndex + 1);
            if ($nextIndex !== $nextOpen) {
                $startIndex = $stringIndex + 1;
                continue;
            }

            return [$thisIndex, $this->tokens[$stringIndex]->text];
        } while ($startIndex < $tokenCount - 1);
    }

    private function findEndIndexOfMethodCall(int $fromIndex): int
    {
        $nextOpen  = $this->getNextTokenIndexOfTypeAndValue(Token::T_RAW_TEXT, '(', $fromIndex);
        $nextClose = $nextOpen;

        $depth = 0;
        do {
            $nextOpen  = $this->getNextTokenIndexOfTypeAndValue(Token::T_RAW_TEXT, '(', $nextOpen + 1);
            $nextClose = $this->getNextTokenIndexOfTypeAndValue(Token::T_RAW_TEXT, ')', $nextClose + 1);

            if ($nextOpen < $nextClose && $nextOpen !== -1) {
                $depth++;
            } else {
                $depth--;
            }
        } while ($depth > 0);

        return $nextClose;
    }

    /**
     * @param Token[][] $arguments
     * @param Token[][] $content
     */
    private function renameIdenticalVariables(array &$arguments, array &$content): void
    {
        $argVarNames = [];
        foreach ($arguments as $argumentTokens) {
            foreach ($argumentTokens as $token) {
                if ($token->id === T_VARIABLE) {
                    $argVarNames[] = $token->text;
                    break 1;
                }
            }
        }

        foreach ($argVarNames as $varName) {
            if ($this->containsVarName($varName)) {
                $this->renameVar($varName, $arguments, $content);
            }
        }
    }

    /**
     * @param string    $varName
     * @param Token[][] $arguments
     * @param Token[][] $contents
     */
    private function renameVar(string $varName, array &$arguments, array &$contents): void
    {
        do {
            $newName = Token::rename($varName);
        } while ($this->containsVarName($newName));

        // update it everywhere
        $newToken = Token::create([T_VARIABLE, $newName]);
        foreach ($arguments as $i => $argumentTokens) {
            foreach ($argumentTokens as $j => $token) {
                if ($token->id === T_VARIABLE && $token->text == $varName) {
                    $arguments[$i][$j] = $newToken;
                }
            }
        }

        foreach ($contents as $i => $content) {
            foreach ($content as $j => $token) {
                if ($token->id === T_VARIABLE && $token->text == $varName) {
                    $contents[$i][$j] = $newToken;
                }
            }
        }
    }

    private function containsVarName(string $varName): bool
    {
        foreach ($this->tokens as $methodToken) {
            if ($methodToken->id == T_VARIABLE && $methodToken->text == $varName) {
                return true;
            }
        }

        return false;
    }

    private function hasEarlyReturn(): bool
    {
        $index = $this->getNextTokenIndexOfType(T_RETURN);
        if ($index === -1) {
            return false;
        }

        $semicolonIndex = $this->getNextTokenIndexOfTypeAndValue(Token::T_RAW_TEXT, ';', $index + 1);

        $overIndex = $this->getNextTokenIndexNotOfTypes(
            [Token::T_RAW_TEXT, T_WHITESPACE, T_STRING, T_COMMENT, T_DOC_COMMENT],
            $semicolonIndex + 1
        );
        /*
                $this->expound();
                echo "INDEX : $index   OVER_INDEX:  $overIndex" . PHP_EOL;
        */
        return ($overIndex !== -1);
    }

}
