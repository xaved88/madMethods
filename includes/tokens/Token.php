<?php
declare(strict_types=1);

namespace MM\tokens;

class Token
{
    public const T_RAW_TEXT = -1;

    public static $NAME_COUNTER = 0;

    /** @var  int */
    public $id;

    /** @var  string */
    public $text;

    /**
     * @param array|string $rawToken
     */
    public function __construct($rawToken)
    {
        if (is_array($rawToken)) {
            $this->id   = $rawToken[0];
            $this->text = $rawToken[1];
        } else {
            $this->id   = static::T_RAW_TEXT;
            $this->text = $rawToken;
        }
    }

    public function getTokenName(): string
    {
        if ($this->id == static::T_RAW_TEXT) {
            return "T_RAW_TEXT";
        }

        return token_name($this->id);
    }

    public static function rename(string $text): string
    {
        $text = preg_replace('/_mm[\d]+/i', '', $text);
        return $text . "_mm" . static::useNameCounter();
    }

    /**
     * NOTE: We want this create method because eventually we can actually just have a static library of tokens that
     * we're checking, instead of always instantiating new ones. This could really help us when it comes to naming
     * replacements too, though the whole scope thing might bite us, so we may need to add that as a bit of an optional
     * parameter. TBD.
     *
     * @param array|string $rawToken
     *
     * @return Token
     */
    public static function create($rawToken): self
    {
        return new self($rawToken);
    }

    private static function useNameCounter(): int
    {
        return static::$NAME_COUNTER++;
    }
}
