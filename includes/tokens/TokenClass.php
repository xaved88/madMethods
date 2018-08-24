<?php
declare(strict_types=1);

namespace MM\tokens;

class TokenClass extends TokenContainer
{
    /** @var  TokenMethod[] */
    private $methods = [];

    public function __construct(array $tokens)
    {
        parent::__construct($tokens);
        $this->init();
    }

    public function optimizeSelf(): array
    {
        $statusReport = [];
        do {
            $replacedMethods  = $statusReport[TokenMethod::REPLACEABLE] ?? [];
            $statusReport         = [TokenMethod::REPLACEABLE => $replacedMethods];
            $replacablesFound = false;
            foreach ($this->methods as $method) {
                $replaceCode = $method->getReplaceableCode();
                if ($replaceCode === TokenMethod::REPLACEABLE) {
                    $this->replaceMethod($method);
                    $replacablesFound = true;
                }
                $statusReport[$replaceCode][] = $method->name;
            }
        } while ($replacablesFound);

        return $statusReport;
    }

    private function init(): void
    {
        $startIndex = 0;
        do {
            $startIndex = $this->sortTokensFromIndex($startIndex);
        } while ($startIndex !== -1);
    }

    private function sortTokensFromIndex(int $startIndex): int
    {
        // find where the function happens
        $functionIndex = -1;
        $tokenCount    = count($this->tokens);
        for ($i = $startIndex; $i < $tokenCount; $i++) {
            if ($this->tokens[$i]->id === T_FUNCTION) {
                $functionIndex = $i;
                break;
            }
        }
        if ($functionIndex === -1) {
            $this->containers[] = new TokenContainer(array_splice($this->tokens, $startIndex));
            return -1;
        }

        // find any modifiers before (public, private, static, abstract, whitespace) til none before, then kill the whitespace
        // that's the method start index
        $methodStartIndex = $this->traverseBackwardsOnTokenTypes(
            $functionIndex,
            [T_PUBLIC, T_PROTECTED, T_PRIVATE, T_ABSTRACT, T_STATIC, T_COMMENT, T_DOC_COMMENT]
        );

        // find the method end index
        $endIndex = $this->getEndBracketIndex($methodStartIndex);

        $this->containers[] = new TokenContainer(
            array_slice($this->tokens, $startIndex, $methodStartIndex - $startIndex)
        );

        $method = new TokenMethod(
            array_slice($this->tokens, $methodStartIndex, $endIndex - $methodStartIndex + 1)
        );

        $this->methods[]    = $method;
        $this->containers[] = $method;

        return $endIndex + 1;
    }

    private function replaceMethod(TokenMethod $method): void
    {
        // Find usages of the method
        $usages = $this->findUsagesOfMethod($method);

        if (!empty($usages)) {

            // Get the method contents
            $arguments = $method->getArguments();
            $contents  = $method->getContents();

            // replace those tokens with the token content of the method
            foreach ($usages as $usage) {
                $this->replaceMethodUsage($usage, $contents, $arguments);
            }
        }

        // remove the method
        $this->removeMethod($method);
    }

    private function findUsagesOfMethod(TokenMethod $method): array
    {
        $usages = [];

        foreach ($this->methods as $methodToCheck) {
            $usage = $methodToCheck->findUsagesOfMemberMethod($method);
            if (!empty($usage)) {
                $usages = array_merge($usages, $usage);
            }
        }

        return $usages;
    }

    /**
     * @param mixed[] $usage
     * @param Token[] $contents
     * @param Token[] $arguments
     */
    private function replaceMethodUsage(array $usage, array $contents, array $arguments): void
    {
        /** @var TokenMethod $method */
        list($startIndex, $endIndex, $method) = $usage;

        $block          = new TokenContainer($contents);
        $returnContents = $block->splitOnReturn();
        if ($returnContents !== null) {
            $method->replaceContentsWithReturn(
                $returnContents[0],
                $returnContents[1],
                $arguments,
                $startIndex,
                $endIndex
            );
        } else {
            $method->replaceContentsWithoutReturn($contents, $arguments, $startIndex, $endIndex);
        }
    }

    private function removeMethod(TokenMethod $method): void
    {
        $containerKey = array_search($method, $this->containers);
        $methodKey    = array_search($method, $this->methods);
        unset($this->containers[$containerKey]);
        unset($this->methods[$methodKey]);
        $this->containers = array_values($this->containers);
        $this->methods    = array_values($this->methods);
    }
}
