import { Injectable } from '@angular/core';
import { SharedService } from './shared.service';
import { UserService } from './user.service';
import { BuilderService } from '../modules/messages-builder/services/builder.service';

@Injectable({
  providedIn: 'root'
})
export class AudienceService {

  public dataUsers = [];
  public subscriberFilterSystem = [];
  public subscriberFilterTags = [];
  public subscriberFilterWidgets = [];
  public subscriberFilterSequences = [];
  public subscriberFilterCustomFields = [];
  public searchFilterValue = '';
  public sendToOptionCriteria = [];
  public sendToOptionValue = [];
  public dataSubscribers: any;
  public countCheckedUsers = 0;
  public disabledBulkCheck = true;
  public countSubsc = 10;
  public savedTags = {
    title: 'Tags',
    conditions: []
  };
  public savedWidgets = {
    title: 'Widgets',
    conditions: []
  };
  public savedSequences = {
    title: 'Autoresponder',
    conditions: []
  };
  public loaderSubscriberCheck = false;

  constructor(
    private readonly _userService: UserService,
    private readonly _builderService: BuilderService,
    private readonly _sharedService: SharedService
  ) { }

  /**
   * Get all subscribers for audience
   * @returns { Promise<void> }
   */
  public async getSubscribers(): Promise<void> {
    this.dataUsers = [];
    this.loaderSubscriberCheck = true;
    this.countSubsc = 10;
    this.disabledBulkCheck = true;
    const data = {
      system: this.subscriberFilterSystem,
      tags: this.subscriberFilterTags,
      widgets: this.subscriberFilterWidgets,
      sequences: this.subscriberFilterSequences,
      customFields: this.subscriberFilterCustomFields
    };

    try {
      const response = await this._userService.getSubscribers(
        this._userService.userID,
        this.searchFilterValue,
        JSON.stringify(data));
      this.loaderSubscriberCheck = false;
      this.dataUsers = response;
      this.dataUsers.forEach((user) => {
        user.selected = true;
      });
      this.countCheckedUsers = this.dataUsers.length;
      this._sharedService.preloaderView = false;
    } catch (err) {
      this._sharedService.showRequestErrors(err);
    }
  }

  /**
   * Getting filters for subscribers by page_id
   * @returns {Promise<void>}
   */
  public async getSubscribersFilter(): Promise<any> {
    try {
      const response = await this._userService.getSubscriberFilter(this._userService.userID);
      this.dataSubscribers = response;
      this.savedWidgets.conditions = response.widgets;
      this.savedTags.conditions = response.tags;
      this.savedSequences.conditions = response.sequences;
      this._builderService.admins = response.admins;
    } catch (err) {
      this._sharedService.showRequestErrors(err);
    }
  }

  /**
   * Create condition filter values list
   * @param type {string}
   */
  public createValueConditionList(type) {
    this.sendToOptionValue = [];
    if (type === 'status') {
      this.sendToOptionValue = [
        {
          name: 'Subscribe',
          value: 'subscribe'
        },
        {
          name: 'Unsubscribe',
          value: 'unsubscribe'
        }
      ];
    } else if (type === 'gender') {
      this.sendToOptionValue = [
        {
          name: 'Male',
          value: 'male',
          subscriberCount: this.dataSubscribers.system.gender.male
        },
        {
          name: 'Female',
          value: 'female',
          subscriberCount: this.dataSubscribers.system.gender.female
        }
      ];
    } else if (type === 'locale') {
      for (const loc of Object.keys(this.dataSubscribers.system.locale)) {
        const dataLocale = {
          name: this.dataSubscribers.system.locale[loc].name,
          value: this.dataSubscribers.system.locale[loc].value,
          subscriberCount: this.dataSubscribers.system.locale[loc].subscriberCount,
        };
        this.sendToOptionValue.push(dataLocale);
      }
    } else if (type === 'language') {
      for (const loc of Object.keys(this.dataSubscribers.system.language)) {
        const dataLocale = {
          name: this.dataSubscribers.system.language[loc].name,
          value: this.dataSubscribers.system.language[loc].value,
          subscriberCount: this.dataSubscribers.system.language[loc].subscriberCount,
        };
        this.sendToOptionValue.push(dataLocale);
      }
    } else if (type === 'timezone') {
      this.sendToOptionValue = [];
    } else if (type === 'firstName') {
      this.dataSubscribers.system.firstName.forEach((names) => {
        this.sendToOptionValue.push({
          name: names,
          value: names
        });
      });
    } else if (type === 'lastName') {
      this.dataSubscribers.system.lastName.forEach((names) => {
        this.sendToOptionValue.push({
          name: names,
          value: names
        });
      });
    } else {
      this.sendToOptionValue = [];
    }
  }

  /**
   * Send criteria values to array
   * @param data {array}
   */
  public criteriaForFilters(data) {
    this.sendToOptionCriteria = data;
  }

  /**
   * Set option to condition filter arrays
   */
  public setOptionToConditionFilter(_statusDraft) {
    this.subscriberFilterSystem = [];
    this.subscriberFilterTags = [];
    this.subscriberFilterWidgets = [];
    this.subscriberFilterSequences = [];
    this.subscriberFilterCustomFields = [];
    this._sharedService.conditionArray.forEach((condition) => {
      if (condition.name === 'status' ||
        condition.name === 'gender' ||
        condition.name === 'locale' ||
        condition.name === 'language' ||
        condition.name === 'timezone' ||
        condition.name === 'firstName' ||
        condition.name === 'lastName' ||
        condition.name === 'dateSubscribed' ||
        condition.name === 'lastInteraction') {
        condition.conditionType = 'system';
        this.subscriberFilterSystem.push(condition);
      } else if (condition.tagID) {
        this.subscriberFilterTags.push({
          criteria: condition.criteria,
          tagID: condition.tagID
        });
      } else if (condition.sequenceID) {
        this.subscriberFilterSequences.push({
          criteria: condition.criteria,
          sequenceID: condition.sequenceID
        });
      } else if (condition.widgetID) {
        this.subscriberFilterWidgets.push({
          criteria: condition.criteria,
          widgetID: condition.widgetID
        });
      } else if (condition.customFieldID) {
        this.subscriberFilterCustomFields.push({
          criteria: condition.criteria,
          value: condition.value,
          type: condition.type,
          customFieldID: condition.customFieldID
        });
      }
    });

    if (_statusDraft === 'true') {
      setTimeout(() => {
        this._builderService.getPointsPosition(this._builderService.config, this._builderService.elem);
        this._builderService.updateDraftItem();
      }, 10);
    } else {
      this.getSubscribers();
    }
  }
}
