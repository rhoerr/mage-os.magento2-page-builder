<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\PageBuilder\Model\Stage\Config;

use Magento\Framework\Config\DataInterfaceFactory;
use Magento\Ui\Config\Converter;

/**
 * Retrieve fields and default values from associated UI component forms for content types
 */
class UiComponentConfig
{
    /**
     * @var DataInterfaceFactory
     */
    private $configFactory;

    /**
     * UiComponentConfig constructor.
     *
     * @param DataInterfaceFactory $configFactory
     */
    public function __construct(
        DataInterfaceFactory $configFactory
    ) {
        $this->configFactory = $configFactory;
    }

    /**
     * Retrieve fields for UI Component
     *
     * @param string $componentName
     *
     * @return array
     */
    public function getFields($componentName) : array
    {
        $componentConfig = $this->configFactory->create(
            ['componentName' => $componentName]
        )->get($componentName);

        $fields = $this->iterateComponents(
            $componentConfig,
            function ($item, $key) {
                // Determine if this item has a formElement key
                if (isset($item[Converter::DATA_ARGUMENTS_KEY]['data']['config']['formElement'])) {
                    $elementConfig
                        = $item[Converter::DATA_ARGUMENTS_KEY]['data']['config'];
                    return [
                        $key => [
                            'default' => (isset($elementConfig['default'])
                                ? $elementConfig['default'] : '')
                        ]
                    ];
                }
            }
        );

        return $fields;
    }

    /**
     * Iterate over components within the configuration and run a defined callback function
     *
     * @param array $config
     * @param \Closure $callback
     * @param bool $key
     *
     * @return array
     */
    private function iterateComponents($config, $callback, $key = false) : array
    {
        $values = $callback($config, $key) ?: [];
        if (isset($config[Converter::DATA_COMPONENTS_KEY])
            && !empty($config[Converter::DATA_COMPONENTS_KEY])
            && (!isset($config[Converter::DATA_ARGUMENTS_KEY]['data']['config']['componentType'])
                || isset($config[Converter::DATA_ARGUMENTS_KEY]['data']['config']['componentType'])
                && $config[Converter::DATA_ARGUMENTS_KEY]['data']['config']['componentType'] !== 'dynamicRows'
            )
        ) {
            foreach ($config[Converter::DATA_COMPONENTS_KEY] as $key => $child) {
                $values = array_merge(
                    $values,
                    $this->iterateComponents($child, $callback, $key) ?: []
                );
            }
        }

        return $values;
    }
}
