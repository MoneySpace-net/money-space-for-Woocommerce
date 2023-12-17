import $ from 'jquery';
import apiFetch from "@wordpress/api-fetch";
import {defaultHooks} from "@wordpress/hooks";
import { 
    getMoneySpaceQueryParams 
} from "@common/utils";

class MNS_BaseGateway {
    constructor({id, context, container = null}) {
        this.initialize();
    }

    initialize() {
        //load the mns script
        this.loadMoneySpaceScript().then(() => {
            // setup required events;
            this.initializeEvents();
            // render the button
            this.createButton();
        });
    }

    isActive() {
        return this.getData() !== null;
    }

    /**
     * Loads the MoneySpace JS SDK
     */
    loadMoneySpaceScript() {
        return new Promise((resolve, reject) => {
            loadMoneySpaceSdk(this.getMoneySpaceSDKArgs()).then(moneySpace => {
                this.moneySpace = moneySpace;
                resolve();
            }).catch(error => {
                console.log(error);
                if (error?.code) {
                    this.submitError(getErrorMessage(error));
                }
                reject();
            })
        })
    }

    /**
     * Returns params used to laod the MoneySpace SDK
     * @returns {{}}
     */
    getMoneySpaceSDKArgs() {
        return getMoneySpaceQueryParams();
    }
}