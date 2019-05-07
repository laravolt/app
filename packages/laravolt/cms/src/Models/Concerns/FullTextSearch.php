<?php
// Taken from https://arianacosta.com/php/laravel/tutorial-full-text-search-laravel-5/
namespace Laravolt\Cms\Models\Concerns;

trait FullTextSearch
{
    /**
     * Replaces spaces with full text search wildcards
     *
     * @param  string $term
     * @return string
     */
    protected function fullTextWildcards($term)
    {
        // removing symbols used by MySQL
        $reservedSymbols = ['-', '+', '<', '>', '@', '(', ')', '~'];
        $term = str_replace($reservedSymbols, '', $term);

        $words = explode(' ', $term);

        foreach ($words as $key => $word) {
            /*
             * applying + operator (required word) only big words
             * because smaller ones are not indexed by mysql
             */
            if (strlen($word) >= 3) {
                $words[$key] = '+'.$word.'*';
            }
        }

        $searchTerm = implode(' ', $words);

        return $searchTerm;
    }

    /**
     * Scope a query that matches a full text search of term.
     *
     * @param  \Illuminate\Database\Eloquent\Builder $query
     * @param  string                                $term
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeSearch($query, $term)
    {
        $columns = implode(',', $this->searchable);

        $query->whereRaw("MATCH ({$columns}) AGAINST (? IN BOOLEAN MODE)", $this->fullTextWildcards($term));
        $query->orderByRaw("MATCH ({$columns}) AGAINST (? IN BOOLEAN MODE) DESC", $this->fullTextWildcards($term));

        return $query;
    }
}
