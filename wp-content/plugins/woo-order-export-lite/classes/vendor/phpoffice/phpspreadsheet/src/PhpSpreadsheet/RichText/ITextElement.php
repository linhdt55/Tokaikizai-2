<?php

namespace WOE\PhpOffice\PhpSpreadsheet\RichText;

use WOE\PhpOffice\PhpSpreadsheet\Style\Font;

interface ITextElement
{
    /**
     * Get text.
     */
    public function getText(): string;

    /**
     * Set text.
     *
     * @param string $text Text
     *
     * @return $this
     */
    public function setText(string $text): self;

    /**
     * Get font.
     */
    public function getFont(): ?Font;

    /**
     * Get hash code.
     *
     * @return string Hash code
     */
    public function getHashCode(): string;
}
