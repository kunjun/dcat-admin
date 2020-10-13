<?php

namespace Dcat\Admin\Grid\Concerns;

use Dcat\Admin\Grid;
use Dcat\Admin\Grid\Tools\ColumnSelector;
use Illuminate\Support\Collection;

trait CanHidesColumns
{
    /**
     * Default columns be hidden.
     *
     * @var array
     */
    public $hiddenColumns = [];

    /**
     * Remove column selector on grid.
     *
     * @param bool $disable
     *
     * @return $this|mixed
     */
    public function disableColumnSelector(bool $disable = true)
    {
        return $this->option('show_column_selector', ! $disable);
    }

    /**
     * @return bool
     */
    public function showColumnSelector(bool $show = true)
    {
        return $this->disableColumnSelector(! $show);
    }

    /**
     * @return bool
     */
    public function allowColumnSelector()
    {
        return $this->option('show_column_selector');
    }

    /**
     * @return string
     */
    public function renderColumnSelector()
    {
        if (! $this->allowColumnSelector()) {
            return '';
        }

        return (new ColumnSelector($this))->render();
    }

    /**
     * Setting default shown columns on grid.
     *
     * @param array|string $columns
     *
     * @return $this
     */
    public function hideColumns($columns)
    {
        if (func_num_args()) {
            $columns = (array) $columns;
        } else {
            $columns = func_get_args();
        }

        $this->hiddenColumns = array_merge($this->hiddenColumns, $columns);

        return $this;
    }

    /**
     * @return string
     */
    public function getColumnSelectorQueryName()
    {
        return $this->makeName(ColumnSelector::SELECT_COLUMN_NAME);
    }

    /**
     * Get visible columns from request query.
     *
     * @return array
     */
    protected function getVisibleColumnsFromQuery()
    {
        $columns = explode(',', request($this->getColumnSelectorQueryName()));

        return array_filter($columns) ?:
            array_values(array_diff($this->columnNames, $this->hiddenColumns));
    }

    /**
     * Get all visible column instances.
     *
     * @return Collection|static
     */
    public function getVisibleColumns()
    {
        $visible = $this->getVisibleColumnsFromQuery();

        if (empty($visible)) {
            return $this->columns;
        }

        array_push($visible, Grid\Column::SELECT_COLUMN_NAME, Grid\Column::ACTION_COLUMN_NAME);

        return $this->columns->filter(function (Grid\Column $column) use ($visible) {
            return in_array($column->getName(), $visible);
        });
    }

    /**
     * Get all visible column names.
     *
     * @return array
     */
    public function getVisibleColumnNames()
    {
        $visible = $this->getVisibleColumnsFromQuery();

        if (empty($visible)) {
            return $this->columnNames;
        }

        array_push($visible, Grid\Column::SELECT_COLUMN_NAME, Grid\Column::ACTION_COLUMN_NAME);

        return collect($this->columnNames)->filter(function ($column) use ($visible) {
            return in_array($column, $visible);
        })->toArray();
    }

    /**
     * Get default visible column names.
     *
     * @return array
     */
    public function getDefaultVisibleColumnNames()
    {
        return array_values(
            array_diff(
                $this->columnNames,
                $this->hiddenColumns,
                [Grid\Column::SELECT_COLUMN_NAME, Grid\Column::ACTION_COLUMN_NAME]
            )
        );
    }
}
