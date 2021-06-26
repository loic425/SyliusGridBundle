<?php

declare(strict_types=1);

namespace App\Grid;

use App\Entity\Book;
use App\Entity\Nationality;
use App\QueryBuilder\EnglishBooksQueryBuilder;
use Sylius\Component\Grid\AbstractGrid;
use Sylius\Component\Grid\Builder\Field;
use Sylius\Component\Grid\Builder\Filter;
use Sylius\Component\Grid\Builder\GridBuilderInterface;

class BookByEnglishAuthorsGrid extends AbstractGrid
{
    private EnglishBooksQueryBuilder $englishBooksQueryBuilder;

    public function __construct(EnglishBooksQueryBuilder $englishBooksQueryBuilder)
    {
        $this->englishBooksQueryBuilder = $englishBooksQueryBuilder;
    }

    public static function getName(): string
    {
        return 'app_book_by_english_authors';
    }

    public static function getResourceClass(): string
    {
        return Book::class;
    }

    protected function buildGrid(GridBuilderInterface $gridBuilder): void
    {
        $gridBuilder
            ->setRepositoryMethod([$this->englishBooksQueryBuilder, 'create'])
            ->addFilter(Filter::create('title', 'string'))
            ->addFilter(Filter::create('author', 'entity')
                ->setFormOptions([
                    'class' => Nationality::class,
                ])
            )
            ->addFilter(Filter::create('nationality', 'entity')
                ->setOptions([
                    'fields' => ['author.nationality'],
                ])
                ->setFormOptions([
                    'class' => Nationality::class,
                ])
            )
            ->orderBy('title', 'asc')
            ->addField(
                Field::create('title', 'string')
                    ->setLabel('Title')
                    ->setSortable(true)
            )
            ->addField(
                Field::create('author', 'string')
                    ->setLabel('Author')
                    ->setPath('author.name')
                    ->setSortable(true, 'author.name')
            )
            ->addField(
                Field::create('nationality', 'string')
                    ->setLabel('Nationality')
                    ->setPath('na.name')
                    ->setSortable(true, 'na.name')
            )
            ->setLimits([10, 5, 15])
        ;
    }
}
