<?php

declare(strict_types=1);

namespace App\Models\ArticlesList;

use DateTimeImmutable;

final class ArticleListRequest
{
    public const MAX_PAGE_SIZE_FOR_LIST = 100;
    public const MIN_PAGE = 1;
    public const MAX_PAGE = 1000;
    public const MIN_PAGE_SIZE = 1;
    public const MAX_PAGE_SIZE = 100;

    private int $page = 1;

    private int $pageSize = self::MAX_PAGE_SIZE_FOR_LIST;

    private SortOrder $sortOrder = SortOrder::RATING;

    private string $query = '';

    /** @var int[] */
    private array $categories = [];

    private ?DateTimeImmutable $dateFrom = null;

    private ?DateTimeImmutable $dateTo = null;

    private ?DateTimeImmutable $trendingDate = null;

    public function getPage(): int
    {
        return $this->page;
    }

    public function setPage(int $page): self
    {
        $this->page = min(self::MAX_PAGE, max(self::MIN_PAGE, $page));
        return $this;
    }

    public function getPageSize(): int
    {
        return $this->pageSize;
    }

    public function setPageSize(int $pageSize): self
    {
        $this->pageSize = min(self::MAX_PAGE_SIZE, max(self::MIN_PAGE_SIZE, $pageSize));
        return $this;
    }

    public function getSortOrder(): SortOrder
    {
        return $this->sortOrder;
    }

    public function setSortOrder(SortOrder $sortOrder): self
    {
        $this->sortOrder = $sortOrder;
        return $this;
    }

    public function getQuery(): string
    {
        return $this->query;
    }

    public function setQuery(string $query): self
    {
        $this->query = $query;
        return $this;
    }

    /** @return int[] */
    public function getCategories(): array
    {
        return $this->categories;
    }

    /** @param  int[]  $categories */
    public function setCategories(array $categories): self
    {
        $this->categories = array_map('intval', $categories);
        return $this;
    }

    public function getDateFrom(): ?DateTimeImmutable
    {
        return $this->dateFrom;
    }

    public function setDateFrom(?DateTimeImmutable $dateFrom): self
    {
        if ($this->dateTo !== null && $dateFrom !== null && $dateFrom > $this->dateTo) {
            $dateFrom = $this->dateTo;
        }
        $this->dateFrom = $dateFrom->modify('00:00:00');
        return $this;
    }

    public function getDateTo(): ?DateTimeImmutable
    {
        return $this->dateTo;
    }

    public function setDateTo(?DateTimeImmutable $dateTo): self
    {
        if ($this->dateFrom !== null && $dateTo !== null && $dateTo < $this->dateFrom) {
            $dateTo = $this->dateFrom;
        }

        $this->dateTo = $dateTo->modify('23:59:59');
        return $this;
    }

    public function getTrendingDate(): ?DateTimeImmutable
    {
        return $this->trendingDate;
    }

    public function setTrendingDate(?DateTimeImmutable $trendingDate): self
    {
        $this->trendingDate = $trendingDate;
        return $this;
    }
}
