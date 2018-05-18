/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

import ko from "knockout";
import Preview from "./content-type/preview";

export class ToolbarOptions {
    public options: KnockoutObservableArray<ToolbarOptionsInterface> = ko.observableArray([]);
    private preview: Preview;

    /**
     * Toolbar Options constructor
     *
     * @param preview
     * @param options
     */
    constructor(preview: Preview, options: ToolbarOptionsInterface[]) {
        this.preview = preview;
        this.options(options);
    }

    get template(): string {
         return "Magento_PageBuilder/content-type/toolbar";
    }

    /**
     * Upon clicking the option update the value as directed
     * When user toggles the option off, set the value back to default
     *
     * @param {ToolbarOptionsInterface} option
     * @param {ToolbarOptionsValueInterface} value
     */
    public onClickOption(option: ToolbarOptionsInterface, value: ToolbarOptionsValueInterface) {
        const defaultValue: string = this.preview.config.fields[option.key].default;
        const currentValue: string = this.preview.previewData[option.key]();
        if (currentValue === value.value) {
            this.preview.updateData(option.key, defaultValue);
        } else {
            this.preview.updateData(option.key, value.value);
        }
    }
}

export interface ToolbarOptionsInterface {
    key: string;
    type: string;
    options: ToolbarOptionsValueInterface[];
}
export interface ToolbarOptionsValueInterface {
    value: string;
    label: string;
    icon: string;
}
