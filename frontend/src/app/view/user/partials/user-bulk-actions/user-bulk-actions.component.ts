import { Component, ElementRef, OnInit, ViewChild } from '@angular/core';
import { SharedService } from '../../../../services/shared.service';
import { NgbModal } from '@ng-bootstrap/ng-bootstrap';
import { UserService } from '../../../../services/user.service';
import { AudienceService } from '../../../../services/audience.service';
import { ToastrService } from 'ngx-toastr';
import { Angular5Csv } from 'angular5-csv/dist/Angular5-csv';

@Component({
  selector: 'app-user-bulk-actions',
  templateUrl: './user-bulk-actions.component.html',
  styleUrls: ['./user-bulk-actions.component.scss']
})
export class UserBulkActionsComponent implements OnInit {
  @ViewChild('content') public content: ElementRef;
  @ViewChild('contentPSID') public contentPSID: ElementRef;
  @ViewChild('contentRemove') public contentRemove: ElementRef;
  @ViewChild('valueField') public valueField: ElementRef;
  @ViewChild('dt1') public dt1: ElementRef;
  @ViewChild('dt2') public dt2: ElementRef;

  public actionList = [
    {
      name: 'Tag hinzufügen',
      type: 'add_tag',
      pro: false
    },
    {
      name: 'Tag entfernen',
      type: 'remove_tag',
      pro: false
    },
    {
      name: 'Zu Autoresponder hinzufügen',
      type: 'subscribe_sequence',
      pro: false
    },
    {
      name: 'Von Autoresponder entfernen',
      type: 'unsubscribe_sequence',
      pro: false
    },
    {
      name: 'Benutzerdefiniertes Feld zuordnen',
      type: 'set_custom_field',
      pro: false
    },
    {
      name: 'Benutzerdefiniertes Feld löschen',
      type: 'clear_custom_field',
      pro: false
    },
    {
      name: 'Vom Bot abmelden',
      type: 'unsubscribe_bot',
      pro: false
    },
    {
      name: 'Vom Bot entfernen',
      type: 'remove_from_bot',
      pro: false
    },
    {
      name: 'Export PSIDs',
      type: 'export_PSIDs',
      pro: true
    }
  ];
  public listActionCheck = true;
  public actionCheck = false;
  public sendAction = '';
  public customValue = '';
  public tagName = '';
  public choiceValue: any;
  public modalActiveClose: any;
  public customFieldAction: any;
  public subscriberCount = 0;
  public closeOutside = true;
  public subscriber = [];

  constructor(
    public readonly _sharedService: SharedService,
    public readonly _audienceService: AudienceService,
    private readonly _modalService: NgbModal,
    private readonly _toastr: ToastrService,
    private readonly _userService: UserService
  ) { }

  ngOnInit() {

  }

  /**
   * Choise bulk action
   * @param type {string}
   */
  public choiseBulkAction(type) {
    this.sendAction = type;
    if (type === 'unsubscribe_bot') {
      this.openVerticallyCentered(this.content);
      this.onClickedOutside();
    } else if (type === 'export_PSIDs') {
      this.openVerticallyCentered(this.contentPSID);
      this.onClickedOutside();
    } else if (type === 'remove_from_bot') {
      this.openVerticallyCentered(this.contentRemove);
      this.onClickedOutside();
    } else {
      setTimeout(() => {
        this.listActionCheck = false;
        this.actionCheck = true;
        this._sharedService.openBulkActions = true;
      } , 10);
    }
  }

  /**
   * Create subscriber actions
   * @param name {object}
   */
  public createSubscriberActions(name) {
    this.closeOutside = false;
    if (name.selectedItems[0]) {
      if (this.sendAction === 'add_tag' || this.sendAction === 'remove_tag') {
        this.tagName = name;
        this._audienceService.savedTags.conditions.forEach((data) => {
          if (name.selectedItems[0]['label'] === data.name) {
            this.choiceValue = data.tagID;
          }
        });
      } else if (this.sendAction === 'subscribe_sequence' || this.sendAction === 'unsubscribe_sequence') {
        this._audienceService.savedSequences.conditions.forEach((data) => {
          if (name.selectedItems[0]['label'] === data.name) {
            this.choiceValue = data.sequenceID;
          }
        });
      } else if (this.sendAction === 'set_custom_field' || this.sendAction === 'clear_custom_field') {
        this._audienceService.dataSubscribers.customFields.forEach((data) => {
          if (name.selectedItems[0]['label'] === data.name) {
            this.choiceValue = data.customFieldID;
            if (this.sendAction === 'set_custom_field') {
              this.customFieldAction = data;
            }
          }
        });
      }
    }
    setTimeout(() => {
      this.closeOutside = true;
    }, 10);
  }

  /**
   * Set boolean value from custom field
   * @param bool {string}
   */
  public booleanValueCustomField(bool) {
    this.customValue = bool;
  }

  /**
   * Choice subscribers actions
   */
  public subscribersActions() {
    this._audienceService.dataUsers.forEach((user) => {
      if (user.selected) {
        this.subscriber.push(user.id);
      }
    });
    switch (this.sendAction) {
      case 'add_tag':
        this.addTagToSubscribers(this.choiceValue);
        break;
      case 'remove_tag':
        this.removeTagToSubscribers(this.choiceValue);
        break;
      case 'subscribe_sequence':
        this.subscribeToSequenceToSubscribers(this.choiceValue).then(() => {
          this.onClickedOutside();
        });
        break;
      case 'unsubscribe_sequence':
        this.unsubscribeFromSequenceToSubscribers(this.choiceValue).then(() => {
          this.onClickedOutside();
        });
        break;
      case 'set_custom_field':
        if (this.customFieldAction.type === 5 ) {
          this.setCustomFieldToSubscribers(this.choiceValue, this.customValue).then(() => {
            this.onClickedOutside();
          });
        } else if (this.customFieldAction.type === 3 ) {
          this.setCustomFieldToSubscribers(this.choiceValue, this.dt1['value']).then(() => {
            this.onClickedOutside();
          });
        } else if (this.customFieldAction.type === 4 ) {
          this.setCustomFieldToSubscribers(this.choiceValue, this.dt2['value']).then(() => {
            this.onClickedOutside();
          });
        } else {
          this.setCustomFieldToSubscribers(this.choiceValue, this.valueField.nativeElement.value).then(() => {
            this.onClickedOutside();
          });
        }
        break;
      case 'clear_custom_field':
        this.clearCustomFieldToSubscribers(this.choiceValue).then(() => {
          this.onClickedOutside();
        });
        break;
      default:
        break;
    }
  }

  /**
   * Add tag to subscribers
   * @param id {number}
   * @returns {Promise<void>}
   */
  public async addTagToSubscribers(id): Promise<any> {
    if (id) {
      const data = {
        tagID: id,
        subscribers: this.subscriber
      };
      try {
        const resp = await this._userService.addTagToSubscribers(this._userService.userID, data);
        this._audienceService.savedTags.conditions.forEach((item) => {
          if (item.tagID === id) {
            item.subscriberCount += resp.counter;
          }
        });
        this.onClickedOutside();
      } catch (err) {
        this._sharedService.showRequestErrors(err);
      }
    } else {
      this.createTag(this.tagName, 'add');
    }
  }

  /**
   * Create subscriber tag
   * @param item {object}
   * @param action {string}
   */
  public async createTag(item, action): Promise<any> {
    try {
      const response = await this._userService.createTag(this._userService.userID, item.selectedItems[0].value);
      this._audienceService.savedTags.conditions.push({
        tagID: response.id,
        name: response.name,
        subscriberCount: 0
      });
      if (action === 'add') {
        this.addTagToSubscribers(response.id);
      } else if (action === 'remove') {
        this.removeTagToSubscribers(response.id);
      }
      this._toastr.success('Tag erstellt');
    } catch (err) {
      this._sharedService.showRequestErrors(err);
    }
  }

  /**
   * Remove tag to subscribers
   * @param id {number}
   * @returns {Promise<void>}
   */
  public async removeTagToSubscribers(id): Promise<any> {
    if (id) {
      const data = {
        tagID: id,
        subscribers: this.subscriber
      };
      try {
        const resp = await this._userService.removeTagToSubscribers(this._userService.userID, data);
        this._audienceService.savedTags.conditions.forEach((item) => {
          if (item.tagID === id) {
            item.subscriberCount -= resp.counter;
          }
        });
        this.onClickedOutside();
      } catch (err) {
        this._sharedService.showRequestErrors(err);
      }
    } else {
      this.createTag(this.tagName, 'remove');
    }
  }

  /**
   * Subscribe to sequence to subscribers
   * @param id {number}
   * @returns {Promise<void>}
   */
  public async subscribeToSequenceToSubscribers(id): Promise<any> {
    const data = {
      sequenceID: id,
      subscribers: this.subscriber
    };
    try {
      const resp = await this._userService.subscribeToSequenceToSubscribers(this._userService.userID, data);
      this._audienceService.savedSequences.conditions.forEach((item) => {
        if (item.sequenceID === id) {
          item.subscriberCount += resp.counter;
        }
      });
    } catch (err) {
      this._sharedService.showRequestErrors(err);
    }
  }

  /**
   * Unsubscribe from sequence to subscribers
   * @param id {number}
   * @returns {Promise<void>}
   */
  public async unsubscribeFromSequenceToSubscribers(id): Promise<any> {
    const data = {
      sequenceID: id,
      subscribers: this.subscriber
    };
    try {
      const resp = await this._userService.unsubscribeFromSequenceToSubscribers(this._userService.userID, data);
      this._audienceService.savedSequences.conditions.forEach((item) => {
        if (item.sequenceID === id) {
          item.subscriberCount -= resp.counter;
        }
      });
    } catch (err) {
      this._sharedService.showRequestErrors(err);
    }
  }

  /**
   * Remove subscribers from bot
   * @returns {Promise<void>}
   */
  public async removeSubscribers(): Promise<void> {
    const data = {
      subscribers: []
    };
    this._audienceService.dataUsers.forEach((user, i) => {
      if (user.selected) {
        data.subscribers.push(user.id);
      }
    });
    data.subscribers.forEach((item) => {
      this._audienceService.dataUsers.forEach((user, i) => {
        if (item === user.id) {

          this._audienceService.dataUsers.splice(i, 1);
        }
      });
    });
    try {
      await this._userService.removeSubscribers(this._userService.userID, data);
      this._audienceService.disabledBulkCheck = false;
      this.modalActiveClose.dismiss();
      this._audienceService.countCheckedUsers = 0;
      this._audienceService.getSubscribersFilter();
    } catch (err) {
      this._sharedService.showRequestErrors(err);
    }
  }

  /**
   * Set Custom Field to subscribers
   * @param id {number}
   * @param value {string}
   * @returns {Promise<void>}
   */
  public async setCustomFieldToSubscribers(id, value): Promise<any> {
    const data = {
      customFieldID: id,
      value: value,
      subscribers: this.subscriber
    };
    try {
      await this._userService.setCustomFieldToSubscribers(this._userService.userID, data);
    } catch (err) {
      this._sharedService.showRequestErrors(err);
    }
  }

  /**
   * Clear Custom Field to subscribers
   * @param id {number}
   * @returns {Promise<void>}
   */
  public async clearCustomFieldToSubscribers(id): Promise<any> {
    const data = {
      customFieldID: id,
      subscribers: this.subscriber
    };
    try {
      await this._userService.clearCustomFieldToSubscribers(this._userService.userID, data);
    } catch (err) {
      this._sharedService.showRequestErrors(err);
    }
  }

  /**
   * Unsubscribe subscribers
   * @returns {Promise<void>}
   */
  public async unsubscribeUsers(): Promise<any> {
    const subscriberArrays = [];
    this._audienceService.dataUsers.forEach((user) => {
      if (user.selected) {
        subscriberArrays.push(user.id);
      }
    });
    const data = {
      subscribers: subscriberArrays
    };
    try {
      const resp = await this._userService.unsubscribeUsers(this._userService.userID, data);
      subscriberArrays.forEach((id) => {
        this._audienceService.dataUsers.forEach((user) => {
          if (id === user.id) {
            user.status = false;
            this._audienceService.dataSubscribers.subscriberSubscribers -= resp.counter;
          }
        });
      });
      this._audienceService.dataSubscribers.unSubscriberSubscribers += resp.counter;
    } catch (err) {
      this._sharedService.showRequestErrors(err);
    }
    this.modalActiveClose.dismiss();
  }

  /**
   * Export PSID subscribers
   * @returns {Promise<void>}
   */
  public async exportPSID(): Promise<any> {
    const subscriberArrays = [];
    this._audienceService.dataUsers.forEach((user) => {
      if (user.selected) {
        subscriberArrays.push(user.id);
      }
    });
    const data = {
      subscribers: subscriberArrays
    };
    try {
      const response = await this._userService.exportPSID(this._userService.userID, data);
      const options = {
        showLabels: true,
        headers: ['psid']
      };

      new Angular5Csv(response, 'PSIDs', options);
      this.modalActiveClose.dismiss();
    } catch (err) {
      this._sharedService.showRequestErrors(err);
    }
  }

  /**
   * Close tags content
   */
  public onClickedOutside() {
    if (this.closeOutside) {
      this._sharedService.openBulkActions = false;
      this.listActionCheck = true;
      this.actionCheck = false;
      this.subscriber = [];
      this.customFieldAction = null;
    }
  }

  /**
   * Open popup
   * @param content {any}
   */
  public openVerticallyCentered(content) {
    this.subscriberCount = 0;
    this._audienceService.dataUsers.forEach((user) => {
      if (user.selected) {
        this.subscriberCount += 1;
      }
    });
    this.modalActiveClose = this._modalService.open(content);
  }

}
