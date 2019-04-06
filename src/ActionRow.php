<?php

namespace Jobcerto\ActionRow;

use Laravel\Nova\Actions\Action;
use Laravel\Nova\Fields\Field;

class ActionRow extends Field
{


      /**
     * Indicates if the element should be shown on the index view.
     *
     * @var bool
     */
      public $showOnIndex = true;

    /**
     * Indicates if the element should be shown on the detail view.
     *
     * @var bool
     */
    public $showOnDetail = false;

    /**
     * Indicates if the element should be shown on the creation view.
     *
     * @var bool
     */
    public $showOnCreation = false;

    /**
     * Indicates if the element should be shown on the update view.
     *
     * @var bool
     */
    public $showOnUpdate = false;

    /**
     * The field's component.
     *
     * @var string
     */
    public $component = 'action-row';


    public function run(Action $action)
    {
    	return  $this->withMeta(['action' => $action]);
    }

      public function icon($icon)
    {
        return $this->withMeta(['icon' => $icon]);
    }

    /**
     * Prepare the field for JSON serialization.
     *
     * @return array
     */
    public function jsonSerialize()
    {
        return array_merge(parent::jsonSerialize(), [
            'indexName' => null
        ]);
    }
}
