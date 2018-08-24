<?php
declare(strict_types=1);

namespace MM\tokens;

class TokenFile extends TokenContainer
{
    /** @var  TokenClass */
    private $tokenClass;

    public function __construct(array $tokens)
    {
        parent::__construct($tokens);
        $this->sortTokensIntoContainers();
    }

    public function optimize(): array
    {
        return $this->tokenClass->optimizeSelf();
    }

    public static function create(string $rawCode): self
    {
        $rawTokens = token_get_all($rawCode);
        $tokens    = [];
        foreach ($rawTokens as $rawToken) {
            $tokens[] = new Token($rawToken);
        }

        return new self($tokens);
    }

    private function sortTokensIntoContainers(): void
    {
        $startIndex = null;
        foreach ($this->tokens as $index => $token) {
            if ($token->id === T_CLASS) {
                $startIndex = $index;
                break;
            }
            if ($token->id === T_INTERFACE) {
                throw new \Exception('class is interface', TokenMethod::CLASS_IS_INTERFACE);
            }
        }

        $endIndex = $this->getEndBracketIndex($startIndex);

        $this->containers[] = new TokenContainer(array_slice($this->tokens, 0, $startIndex));
        $this->tokenClass   = new TokenClass(array_slice($this->tokens, $startIndex, $endIndex - 1 - $startIndex));
        $this->containers[] = $this->tokenClass;
        $this->containers[] = new TokenContainer(array_slice($this->tokens, $endIndex - 1));
    }
}
