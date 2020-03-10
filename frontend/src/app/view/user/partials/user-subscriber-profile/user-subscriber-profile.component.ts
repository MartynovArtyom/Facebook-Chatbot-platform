import {Component, ElementRef, Input, OnChanges, OnInit, ViewChild} from '@angular/core';
import { SubscriberUser } from '../../../../../entities/models-user';
import { ToastrService } from 'ngx-toastr';
import { AudienceService } from '../../../../services/audience.service';
import { UserService } from '../../../../services/user.service';
import { SharedService } from '../../../../services/shared.service';

@Component({
  selector: 'app-user-subscriber-profile',
  templateUrl: './user-subscriber-profile.component.html',
  styleUrls: ['./user-subscriber-profile.component.scss']
})
export class UserSubscriberProfileComponent implements OnInit, OnChanges {

  @ViewChild('valueField') public valueField: ElementRef;
  @ViewChild('dt2') public dt2: ElementRef;
  public _subscriberId: any;
  @Input() closePopup;

  @Input('subscriberId') set subscriberId(subscriberId) {
    if (subscriberId) {
      this._subscriberId = subscriberId;
    }
  }
  get subscriberId() {
    return this._subscriberId;
  }

  public subscriber = new SubscriberUser({});
  public allTags = [];
  public allSequences = [];
  public allCustomFields = [];
  public subscriberTags = [];
  public subscriberSequences = [];
  public subscriberWidgets = [];
  public preloader = true;
  public addTagsCheck = false;
  public addSequencesCheck = false;
  public customFiledsCheck = true;
  public customFiledsCount = 5;
  public cfPanel: any;

  constructor(
    private readonly _sharedService: SharedService,
    public readonly _userService: UserService,
    public readonly _audienceService: AudienceService,
    private readonly _toastr: ToastrService
  ) { }

  ngOnInit() {
  }

  ngOnChanges() {
    this.getSubscriberForId();
  }

  /**
   * Close subscriber profile
   */
  public closePopups() {
    this.closePopup();
  }

  /**
   * Get subscriber for id
   * @returns {Promise<void>}
   */
  public async getSubscriberForId(): Promise<void> {
    try {
      const response = await this._userService.getSubscriberForId(this._userService.userID, this._subscriberId);
      this.subscriber = response.details;
      this._userService.subscriberInfo = response;
      this.subscriberTags = response.subscriberTags;
      this.subscriberSequences = response.subscriberSequences;
      this.subscriberWidgets = response.subscriberWidgets;

      response.allTags.forEach((tag) => {
        let count = 0;
        this.subscriberTags.forEach((item) => {
          if (item.tagID === tag.tagID) {
            count++;
          }
        });
        if (count === 0) {
          this.allTags.push(tag);
        }
      });
      response.allSequences.forEach((tag) => {
        let count = 0;
        this.subscriberSequences.forEach((item) => {
          if (item.sequenceID === tag.sequenceID) {
            count++;
          }
        });
        if (count === 0) {
          this.allSequences.push(tag);
        }
      });
      response.allCustomFields.forEach((item) => {
        item['activated'] = false;
        this.allCustomFields.push(item);
      });
      this.preloader = false;
    } catch (err) {
      this._sharedService.showRequestErrors(err);
    }
  }

  /**
   * Set boolean value from custom field
   * @param item {object}
   * @param bool {string}
   */
  public booleanValueCustomField(item, bool) {
    item.value = bool;
  }

  /**
   * Open add tags content
   */
  public openAddTags() {
    setTimeout(() => {
      this.addTagsCheck = !this.addTagsCheck;
    }, 10);
  }

  /**
   * Open add sequence content
   */
  public openAddSequence() {
    setTimeout(() => {
      this.addSequencesCheck = !this.addSequencesCheck;
    }, 10);
  }

  /**
   * Close tags content
   * @param e {Event}
   */
  public onClickedOutsideTags(e: Event) {
    setTimeout(() => {
      this.addTagsCheck = false;
    }, 5);
  }

  /**
   * Close custom fields content
   * @param e {Event}
   */
  public onClickedOutsideCustomFields(e: Event) {
    /*this.allCustomFields.forEach((item) => {
      item['activated'] = false;
    });*/
  }

  /**
   * Show custom field option
   * @param item {object}
   */
  public showCustomFieldOption(item) {
    if (this._userService.userPageInfo.role !== 4) {
      if (this.cfPanel) {
        this.cfPanel['activated'] = false;
      }
      setTimeout(() => {
        this.cfPanel = item;
        item['activated'] = true;
      }, 20);
    }
  }

  /**
   * Clear and close custom field content
   * @param item {object}
   */
  public closeCustomField(item) {
    item.value = null;
    this.removeCustomFieldFromSubscriber(item.customFieldID);
    setTimeout(() => {
      item['activated'] = false;
    }, 25);
  }

  /**
   * Clear and close custom field content
   * @param item {object}
   */
  public closePanel(item) {
    setTimeout(() => {
      item['activated'] = false;
    }, 25);
  }

  /**
   * Close sequences content
   * @param e {Event}
   */
  public onClickedOutsideSequences(e: Event) {
    setTimeout(() => {
      this.addSequencesCheck = false;
    }, 5);
  }

  /**
   * Created subscriber actions
   * @param name {string}
   * @param all {array}
   * @param subscriber {array}
   */
  public createSubscriberActions(name, all, subscriber) {
    if (name.selectedValues[0]['$ngOptionLabel']) {
      all.forEach((item, i) => {
        if (item.name === name.selectedValues[0]['$ngOptionLabel']) {
          subscriber.push(item);
          all.splice(i, 1);
          if (item.tagID) {
            this.addTagFromSubscriber(item.tagID);
          } else if (item.sequenceID) {
            this.addSequenceFromSubscriber(item.sequenceID);
          }
        }
      });
    } else {
      this.createTag(name.selectedValues[0], subscriber);
    }
  }

  /**
   * Create subscriber tag
   * @param item {object}
   * @param subscriber {array}
   */
  public async createTag(item, subscriber): Promise<any> {
    try {
      const response = await this._userService.createTag(this._userService.userID, item);
      subscriber.push({
        tagID: response.id,
        name: response.name
      });
      this.addTagFromSubscriber(response.id);
      this._audienceService.savedTags.conditions.push({
        tagID: response.id,
        name: response.name,
        subscriberCount: 0
      });
      this._toastr.success('Tag erstellt');
    } catch (err) {
      this._sharedService.showRequestErrors(err);
    }
  }

  /**
   * Delete subscriber actions
   * @param item {object}
   * @param all {array}
   * @param subscriber {array}
   */
  public deleteFromSubscriberAction(item, all, subscriber) {
    all.push(item);
    subscriber.forEach((tag, i) => {
      if (item === tag) {
        subscriber.splice(i, 1);
        if (item.tagID) {
          this.removeTagFromSubscriber(item.tagID);
        } else if (item.sequenceID) {
          this.removeSequenceFromSubscriber(item.sequenceID);
        }
      }
    });
  }

  /**
   * Add tag from subscriber
   * @param tagID {number}
   * @returns {Promise<void>}
   */
  public async addTagFromSubscriber(tagID): Promise<void> {
    const data = {
      tagID: tagID,
      subscribers: [
        this.subscriber.id
      ]
    };
    try {
      const resp = await this._userService.addTagFromSubscriber(this._userService.userID, data);
      this._audienceService.savedTags.conditions.forEach((item) => {
        if (item.tagID === tagID) {
          item.subscriberCount += resp.counter;
        }
      });
    } catch (err) {
      this._sharedService.showRequestErrors(err);
    }
  }

  /**
   * Add sequence from subscriber
   * @param sequenceID {number}
   * @returns {Promise<void>}
   */
  public async addSequenceFromSubscriber(sequenceID): Promise<void> {
    const data = {
      sequenceID: sequenceID,
      subscribers: [
        this.subscriber.id
      ]
    };
    try {
      const resp = await this._userService.addSequenceFromSubscriber(this._userService.userID, data);
      this._audienceService.savedSequences.conditions.forEach((item) => {
        if (item.sequenceID === sequenceID) {
          item.subscriberCount += resp.counter;
        }
      });
    } catch (err) {
      this._sharedService.showRequestErrors(err);
    }
  }

  /**
   * Remove tag from subscriber
   * @param tagID {number}
   * @returns {Promise<void>}
   */
  public async removeTagFromSubscriber(tagID): Promise<void> {
    const data = {
      tagID: tagID,
      subscribers: [
        this.subscriber.id
      ]
    };
    try {
      const resp = await this._userService.removeTagFromSubscriber(this._userService.userID, data);
      this._audienceService.savedTags.conditions.forEach((item) => {
        if (item.tagID === tagID) {
          item.subscriberCount -= resp.counter;
        }
      });
    } catch (err) {
      this._sharedService.showRequestErrors(err);
    }
  }

  /**
   * Remove sequence from subscriber
   * @param sequenceID {number}
   * @returns {Promise<void>}
   */
  public async removeSequenceFromSubscriber(sequenceID): Promise<void> {
    const data = {
      sequenceID: sequenceID,
      subscribers: [
        this.subscriber.id
      ]
    };
    try {
      const resp = await this._userService.removeSequenceFromSubscriber(this._userService.userID, data);
      this._audienceService.savedSequences.conditions.forEach((item) => {
        if (item.sequenceID === sequenceID) {
          item.subscriberCount -= resp.counter;
        }
      });
    } catch (err) {
      this._sharedService.showRequestErrors(err);
    }
  }

  /**
   * Set value custom fields
   * @param item {object}
   * @param valueField {string}
   */
  public setValueCustomField(item, valueField) {
    if (item.type === 3 || item.type === 4) {
      this.addCustomFieldFromSubscriber(item.customFieldID, item.value);
    } else if (item.type !== 5) {
      item.value = this.valueField.nativeElement.value;
      this.addCustomFieldFromSubscriber(item.customFieldID, this.valueField.nativeElement.value);
    } else {
      item.value = valueField;
      this.addCustomFieldFromSubscriber(item.customFieldID, valueField);
    }
    setTimeout(() => {
      item['activated'] = false;
    }, 35);
  }

  /**
   * Add custom field from subscriber
   * @param customFieldID {number}
   * @param value {string}
   * @returns {Promise<void>}
   */
  public async addCustomFieldFromSubscriber(customFieldID, value): Promise<void> {
    const data = {
      customFieldID: customFieldID,
      value: value,
      subscribers: [
        this.subscriber.id
      ]
    };
    try {
      await this._userService.addCustomFieldFromSubscriber(this._userService.userID, data);
    } catch (err) {
      this._sharedService.showRequestErrors(err);
    }
  }

  /**
   * Add custom field from subscriber
   * @param customFieldID {number}
   * @returns {Promise<void>}
   */
  public async removeCustomFieldFromSubscriber(customFieldID): Promise<void> {
    const data = {
      customFieldID: customFieldID,
      subscribers: [
        this.subscriber.id
      ]
    };
    try {
      await this._userService.removeCustomFieldFromSubscriber(this._userService.userID, data);
    } catch (err) {
      this._sharedService.showRequestErrors(err);
    }
  }

  /**
   * Show more custom fields
   * @param value {number}
   */
  public showMoreCustomFields(value) {
    this.customFiledsCount = value;
    this.customFiledsCheck = !this.customFiledsCheck;
  }

  /**
   * Unsubscribe subscribers
   * @returns {Promise<void>}
   */
  public async unsubscribeProfile(): Promise<any> {
    const subscriberArrays = [];
    const data = {
      subscribers: [this.subscriber.id]
    };
    try {
      const resp = await this._userService.unsubscribeUsers(this._userService.userID, data);
      this._audienceService.dataUsers.forEach((user) => {
        if (this.subscriber.id === user.id) {
          user.status = false;
        }
      });
      this.subscriber.status = false;
      this._audienceService.dataSubscribers.unSubscriberSubscribers += resp.counter;
      this.closePopup();
    } catch (err) {
      this._sharedService.showRequestErrors(err);
    }
  }

}
