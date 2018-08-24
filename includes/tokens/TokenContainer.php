<?php
declare(strict_types=1);

namespace MM\tokens;

use Exception;

class TokenContainer
{
    /** @var  Token[] */
    protected $tokens;

    /** @var  TokenContainer[] */
    protected $containers = [];

    public function __construct(array $tokens)
    {
        $this->tokens = $tokens;
    }

    /**
     * @return Token[]
     */
    public function getTokens(): array
    {
        return $this->tokens;
    }

    public function export(): string
    {
        $outString = '';
        if (empty($this->containers)) {
            foreach ($this->tokens as $token) {
                $outString .= $token->text;
            }
        } else {
            foreach ($this->containers as $container) {
                $outString .= $container->export();
            }
        }

        return $outString;
    }

    public function splitOnReturn(): ?array
    {
        $returnIndex = $this->getNextTokenIndexOfType(T_RETURN);
        if ($returnIndex === -1) {
            return null;
        }

        $preEndIndex    = $this->getPrevTokenIndexNotOfType(T_WHITESPACE, $returnIndex - 1);
        $postStartIndex = $this->getNextTokenIndexNotOfType(T_WHITESPACE, $returnIndex + 1);
        $postEndIndex   = $this->getNextTokenIndexOfTypeAndValue(Token::T_RAW_TEXT, ';', $postStartIndex + 1);

        $beforeReturn = $preEndIndex > 0 ? array_slice($this->tokens, 0, $preEndIndex + 1) : [];
        $afterReturn  = array_slice($this->tokens, $postStartIndex, $postEndIndex - $postStartIndex);
        return [$beforeReturn, $afterReturn];
    }

    protected function getEndBracketIndex(int $startIndex): int
    {
        $tokenCount          = count($this->tokens);
        $currentBracketDepth = 0;
        for ($i = $startIndex; $i < $tokenCount; $i++) {
            if ($this->tokens[$i]->id !== Token::T_RAW_TEXT) {
                continue;
            }

            if ($this->tokens[$i]->text == '{') {
                $currentBracketDepth++;
            } elseif ($this->tokens[$i]->text == '}') {
                $currentBracketDepth--;
                if ($currentBracketDepth === 0) {
                    return $i;
                }
            }
        }

        throw new Exception('brackets don\'t close up yo');
    }

    protected function getNextTokenIndexOfType(int $type, int $startIndex = 0): int
    {
        $tokenCount = count($this->tokens);
        for ($i = $startIndex; $i < $tokenCount; $i++) {
            if ($this->tokens[$i]->id === $type) {
                return $i;
            }
        }

        return -1;
    }

    protected function getNextTokenIndexNotOfType(int $type, int $startIndex = 0): int
    {
        $tokenCount = count($this->tokens);
        for ($i = $startIndex; $i < $tokenCount; $i++) {
            if ($this->tokens[$i]->id !== $type) {
                return $i;
            }
        }

        return -1;
    }

    protected function getNextTokenIndexNotOfTypes(array $types, int $startIndex = 0): int
    {
        $tokenCount = count($this->tokens);
        for ($i = $startIndex; $i < $tokenCount; $i++) {
            if (!in_array($this->tokens[$i]->id, $types)) {
                return $i;
            }
        }

        return -1;
    }

    protected function getNextTokenIndexOfTypeAndValue(int $type, string $value, int $startIndex = 0): int
    {
        $tokenCount = count($this->tokens);
        for ($i = $startIndex; $i < $tokenCount; $i++) {
            if ($this->tokens[$i]->id === $type && $this->tokens[$i]->text === $value) {
                return $i;
            }
        }

        return -1;
    }

    protected function getPrevTokenIndexNotOfType(int $type, int $startIndex = 0): int
    {
        for ($i = $startIndex; $i > -1; $i--) {
            if ($this->tokens[$i]->id !== $type) {
                return $i;
            }
        }

        return -1;
    }

    protected function getPrevTokenIndexOfTypeAndValue(int $type, string $value, int $startIndex = 0): int
    {
        for ($i = $startIndex; $i > -1; $i--) {
            if ($this->tokens[$i]->id === $type && $this->tokens[$i]->text === $value) {
                return $i;
            }
        }

        return -1;
    }

    protected function traverseBackwardsOnTokenTypes(
        int $startIndex,
        array $includeTypes,
        array $allowableTypes = [T_WHITESPACE]
    ): int {

        $allowableTypes = array_merge($includeTypes, $allowableTypes);
        $bestIndex      = $startIndex;
        for ($i = $startIndex - 1; $i > -1; $i--) {
            if (!in_array($this->tokens[$i]->id, $allowableTypes)) {
                break;
            }
            if (in_array($this->tokens[$i]->id, $includeTypes)) {
                $bestIndex = $i;
            }
        }

        return $bestIndex;
    }

    protected function getAllTokenIndexsOfTypeInRange(int $type, int $startIndex, int $endIndex): array
    {
        $indexes = [];
        while (true) {
            $startIndex = $this->getNextTokenIndexOfType($type, $startIndex + 1);
            if ($startIndex === -1 || $startIndex > $endIndex) {
                break;
            }
            $indexes[] = $startIndex;
        }

        return $indexes;
    }

    /**
     * @param Token[]|null $tokens
     *
     * @return Token[]
     */
    protected function trimTokens(?array $tokens = null): array
    {
        if ($tokens === null) {
            $tokens = $this->tokens;
        }

        while (!empty($tokens) && reset($tokens)->id == T_WHITESPACE) {
            array_shift($tokens);
        }

        while (!empty($tokens) && end($tokens)->id == T_WHITESPACE) {
            array_pop($tokens);
        }

        return $tokens;
    }

    /** @param Token[] $tokens */
    protected function expound(?array $tokens = null): void
    {
        if ($tokens === null) {
            $tokens = $this->tokens;
        }

        foreach ($tokens as $index => $token) {
            echo $index . ". " . $token->getTokenName() . " :: " . $token->text . PHP_EOL;
        }
    }
}
