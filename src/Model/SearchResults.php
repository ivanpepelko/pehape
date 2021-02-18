<?php

namespace App\Model;

/**
 * @author Ivan Pepelko <ivan.pepelko@gmail.com>
 */
class SearchResults
{
    public function __construct(
        private string $term,
        public iterable $articles = [],
        public iterable $galleries = [],
        public iterable $questions = [],
        public iterable $answers = []
    ) {
    }

    public function getTerm(): string
    {
        return $this->term;
    }

    public function getTotalCount(): int
    {
        return count($this->articles) + count($this->galleries) + count($this->questions) + count($this->answers);
    }
}
