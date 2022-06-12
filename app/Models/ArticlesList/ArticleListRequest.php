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

    private string $sortOrder = SortOrder::RATING;

    private string $query = '';

    /** @var int[] */
    private array $categories = [];

    private ?DateTimeImmutable $dateFrom = null;

    private ?DateTimeImmutable $dateTo = null;

    private ?DateTimeImmutable $trendingDate = null;

    /**
     * @return int
     */
    public function getPage(): int
    {
        return $this->page;
    }

    /**
     * @param  int  $page
     * @return ArticleListRequest
     */
    public function setPage(int $page): self
    {
        $this->page = min(self::MAX_PAGE, max(self::MIN_PAGE, $page));
        return $this;
    }

    /**
     * @return int
     */
    public function getPageSize(): int
    {
        return $this->pageSize;
    }

    /**
     * @param  int  $pageSize
     * @return ArticleListRequest
     */
    public function setPageSize(int $pageSize): self
    {
        $this->pageSize = min(self::MAX_PAGE_SIZE, max(self::MIN_PAGE_SIZE, $pageSize));
        return $this;
    }

    /**
     * @return string
     */
    public function getSortOrder(): string
    {
        return $this->sortOrder;
    }

    /**
     * @param  string  $sortOrder
     * @return ArticleListRequest
     */
    public function setSortOrder(string $sortOrder): self
    {
        if (!in_array($sortOrder, SortOrder::ORDERS, true)) {
            $sortOrder = SortOrder::RATING;
        }
        $this->sortOrder = $sortOrder;
        return $this;
    }

    /**
     * @return string
     */
    public function getQuery(): string
    {
        return $this->query;
    }

    /**
     * @param  string  $query
     * @return ArticleListRequest
     */
    public function setQuery(string $query): self
    {
        $this->query = $query;
        return $this;
    }

    /**
     * @return int[]
     */
    public function getCategories(): array
    {
        return $this->categories;
    }

    /**
     * @param  int[]  $categories
     */
    public function setCategories(array $categories): self
    {
        $this->categories = array_map('intval', $categories);
        return $this;
    }

    /**
     * @return DateTimeImmutable|null
     */
    public function getDateFrom(): ?DateTimeImmutable
    {
        return $this->dateFrom;
    }

    /**
     * @param  DateTimeImmutable|null  $dateFrom
     * @return ArticleListRequest
     */
    public function setDateFrom(?DateTimeImmutable $dateFrom): self
    {
        if ($this->dateTo !== null && $dateFrom !== null && $dateFrom > $this->dateTo) {
            $dateFrom = $this->dateTo;
        }
        $this->dateFrom = $dateFrom->modify('00:00:00');
        return $this;
    }

    /**
     * @return DateTimeImmutable|null
     */
    public function getDateTo(): ?DateTimeImmutable
    {
        return $this->dateTo;
    }

    /**
     * @param  DateTimeImmutable|null  $dateTo
     * @return ArticleListRequest
     */
    public function setDateTo(?DateTimeImmutable $dateTo): self
    {
        if ($this->dateFrom !== null && $dateTo !== null && $dateTo < $this->dateFrom) {
            $dateTo = $this->dateFrom;
        }

        $this->dateTo = $dateTo->modify('23:59:59');
        return $this;
    }

    /**
     * @return DateTimeImmutable|null
     */
    public function getTrendingDate(): ?DateTimeImmutable
    {
        return $this->trendingDate;
    }

    /**
     * @param  DateTimeImmutable|null  $trendingDate
     * @return ArticleListRequest
     */
    public function setTrendingDate(?DateTimeImmutable $trendingDate): self
    {
        $this->trendingDate = $trendingDate;
        return $this;
    }
}
