<?php
declare(strict_types=1);

namespace MM\tokens;

class TokenMethodArgumentContainer extends TokenContainer
{

    public static function create(TokenContainer $tokenContainer, int $startIndex, int $endIndex): self
    {
        $startIndex = $tokenContainer->getNextTokenIndexOfTypeAndValue(Token::T_RAW_TEXT, '(', $startIndex) + 1;
        $endIndex   = $tokenContainer->getPrevTokenIndexOfTypeAndValue(Token::T_RAW_TEXT, ')', $endIndex);

        // get the values being passed to the method
        return new TokenMethodArgumentContainer(
            array_slice(
                $tokenContainer->getTokens(),
                $startIndex,
                $endIndex - $startIndex
            )
        );
    }

    /**
     * @return Token[][]
     */
    public function getArgumentValues(): array
    {
        $values     = [];
        $startIndex = 0;

        while (true) {
            $nextIndex = $this->getNextTokenIndexOfTypeAndValue(Token::T_RAW_TEXT, ',', $startIndex);
            if ($nextIndex == -1) {
                $values[] = array_slice($this->tokens, $startIndex);
                break;
            }

            $values[]   = array_slice($this->tokens, $startIndex, $nextIndex - $startIndex);
            $startIndex = $nextIndex + 1;
        }

        foreach ($values as $index => $value) {
            $values[$index] = $this->trimTokens($value);
        }

        return $values;
    }

    public function getArgumentValuesWithoutTypeHints(): array
    {
        // I'm sorry... we'll rework this someday :)
        $tokenBackup = $this->tokens;

        $values = $this->getArgumentValues();
        foreach ($values as $index => $value) {
            $this->tokens = $value;
            $startIndex   = $this->getNextTokenIndexOfType(T_VARIABLE);
            if ($startIndex !== 0) {
                $values[$index] = array_slice($value, $startIndex);
            }
        }

        $this->tokens = $tokenBackup;

        return $values;
    }
}
