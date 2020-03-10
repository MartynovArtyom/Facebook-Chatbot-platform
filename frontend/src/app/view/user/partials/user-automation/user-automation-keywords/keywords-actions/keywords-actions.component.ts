import { Component, ElementRef, Input, OnInit, ViewChild } from '@angular/core';
import { Angular5Csv } from 'angular5-csv/dist/Angular5-csv';
import { UserService } from '../../../../../../services/user.service';
import { ToastrService } from 'ngx-toastr';
import { NgbModal } from '@ng-bootstrap/ng-bootstrap';
import { AudienceService } from '../../../../../../services/audience.service';
import { SharedService } from '../../../../../../services/shared.service';
import { BuilderService } from '../../../../../../modules/messages-builder/services/builder.service';
import { BuilderFunctionsService } from '../../../../../../modules/messages-builder/services/builder-functions.service';

@Component({
  selector: 'app-keywords-actions',
  templateUrl: './keywords-actions.component.html',
  styleUrls: ['./keywords-actions.component.scss']
})
export class KeywordsActionsComponent implements OnInit {
  @Input() public _openind: any;
  @Input('openind') set openind(openind) {
    if (openind) {
      this._openind = openind;
    }
  }
  get openind() {
    return this._openind;
  }
  @ViewChild('content') public content: ElementRef;
  @ViewChild('contentPSID') public contentPSID: ElementRef;
  @ViewChild('contentRemove') public contentRemove: ElementRef;
  @ViewChild('valueField') public valueField: ElementRef;

  public actionList = [
    {
      name: 'Tag hinzufügen',
      type: 'add_tag'
    },
    {
      name: 'Tag entfernen',
      type: 'remove_tag'
    },
    {
      name: 'Abonnieren Sie die Sequenz',
      type: 'subscribe_sequence'
    },
    {
      name: 'Sequenz abbestellen',
      type: 'unsubscribe_sequence'
    },
    {
      name: 'Konversation als offen markieren',
      type: 'mark_conversation_open'
    },
    {
      name: 'Administratoren benachrichtigen',
      type: 'notify_admins'
    },
    {
      name: 'Benutzerdefiniertes Feld festlegen',
      type: 'set_custom_field'
    },
    {
      name: 'Benutzerdefiniertes Teilnehmerfeld löschen',
      type: 'clear_subscriber_custom_field'
    },
    {
      name: 'Abonniere bot',
      type: 'subscribe_bot'
    },
    {
      name: 'Bot abbestellen',
      type: 'unsubscribe_bot'
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
  public actionsKey = [];

  constructor(
    public readonly _sharedService: SharedService,
    public readonly _audienceService: AudienceService,
    private readonly _modalService: NgbModal,
    public readonly _builderService: BuilderService,
    public readonly _builderFunctionsService: BuilderFunctionsService,
    private readonly _toastr: ToastrService,
    private readonly _userService: UserService
  ) { }

  ngOnInit() {
    this.actionsKey = JSON.parse(JSON.stringify(this._openind.actions));

  }

  public activeCalendar(action, status) {
    action['activeDatepicker'] = status;
  }

  /**
   * Get current position action notification text
   * @param action {object}
   * @param elem {any}
   * @param item {object}
   * @param key {string}
   */
  public getCaretPosAction(action, elem, item, key) {
    elem.value = elem.value.substring(0, elem.selectionStart) +
      action.desc + elem.value.substring(elem.selectionStart, elem.value.length);

    item[key] = elem.value;

    this._builderFunctionsService.closeSubTextareaPanelAction(item);
    setTimeout(() => {
      elem.blur();
      this._openind['actionsActive'] = true;
    }, 10);
  }

  /**
   * Open admins
   * @param status {boolean}
   * @param action {object}
   */
  public openAdmins(status, action) {
    action['openAdmins'] = status;

    setTimeout(() => {
      this._openind['actionsActive'] = true;
    } , 10);
  }

  /**
   * Add emoji
   * @param oField {object}
   * @param $event {event}
   * @param item {object}
   * @param key {string}
   */
  public addEmoji(oField, $event, item, key) {
    oField.value = oField.value.substring(0, oField.selectionStart) +
      $event + oField.value.substring(oField.selectionStart, oField.value.length);
    item[key] = oField.value;

    setTimeout(() => {
      this._openind['actionsActive'] = true;
    } , 10);
  }

  /**
   * Set input value to object
   * @param object {object)
   * @param key {string}
   * @param value {string}
   */
  public setInputValue(object, key, value) {
    object[key] = value;
    // this.saveKeywordActions();

    setTimeout(() => {
      this._openind['actionsActive'] = true;
    } , 10);
  }

  /**
   * Choice admin action status
   * @param admin {object}
   * @param team {array}
   */
  public choiceAdminActionStatus(admin, team) {
    admin.status = !admin.status;

    if (admin.status === true) {
      const item = team.find(link => link.adminID === admin.adminID);
      if (item) {
        team.forEach((person, index) => {
          if (person.adminID === admin.adminID) {
            team.splice(index, 1);
          }
        });
      } else {
        team.push(admin);
      }
    } else {
      team.forEach((person, index) => {
        if (person.adminID === admin.adminID) {
          team.splice(index, 1);
        }
      });
    }
    this.saveKeywordActions();
    setTimeout(() => {
      this._openind['actionsActive'] = true;
    } , 10);
  }

  /**
   * Choise bulk action
   * @param type {string}
   */
  public choiseBulkAction(type) {
    this.actionsKey.push({
      type: type
    });
    if (type === 'mark_conversation_open' || type === 'subscribe_bot' || type === 'unsubscribe_bot') {
      // this.saveKeywordActions();
    }
    if (type === 'notify_admins') {
      this.actionsKey[this.actionsKey.length - 1]['team'] = [];
    }
    setTimeout(() => {
      this._openind['actionsActive'] = true;
    } , 10);
  }


  /**
   * Create tag
   * @param action {object}
   * @param value {string}
   * @returns {Promise<void>}
   */
  public async createTags(action, value): Promise<any> {
    try {
      const response = await this._userService.createTag(this._userService.userID, value.label);
      this._toastr.success('Tag erstellt');
      this._audienceService.savedTags.conditions.push({
        tagID: response['id'],
        name: response['name']
      });
      action.id = response['id'];
      action.name = response['name'];
      // this.saveKeywordActions();
    } catch (err) {
      this._sharedService.showRequestErrors(err);
    }
  }

  /**
   * Create subscriber actions
   * @param name {object}
   * @param type {string}
   * @param action {object}
   */
  public createSubscriberActions(name, type, action) {
    this.closeOutside = false;
    if (type === 'add_tag' ||
        type === 'remove_tag') {
      let count = 0;
      this._audienceService.savedTags.conditions.forEach((item) => {
        if (item.name === name.label) {
          count++;
          action.name = item.name;
          action.id = item.tagID;
          // this.saveKeywordActions();
        }
      });
      if (count === 0) {
        this.createTags(action, name);
      }
    } else if (type === 'subscribe_sequence' ||
               type === 'unsubscribe_sequence') {
      this._audienceService.savedSequences.conditions.forEach((item) => {
        if (item.name === name.label) {
          action.name = item.name;
          action.id = item.sequenceID;
          // this.saveKeywordActions();
        }
      });
    } else if (type === 'clear_subscriber_custom_field') {
      this._audienceService.dataSubscribers.customFields.forEach((item) => {
        if (item.name === name.label) {
          action.name = item.name;
          action.id = item.customFieldID;
          // this.saveKeywordActions();
        }
      });
    } else if (type === 'set_custom_field') {
      this._audienceService.dataSubscribers.customFields.forEach((item) => {
        if (item.name === name.label) {
          action.name = item.name;
          action.id = item.customFieldID;
          action.typeCF = item.type;
          action.value = null;
        }
      });
    }
    setTimeout(() => {
      this.closeOutside = true;
    }, 10);
  }

  /**
   * Delete action
   * @param actions {object}
   * @param i {number}
   */
  public deleteAction(actions, i) {
    actions.splice(i, 1);
    // this.saveKeywordActions();
    setTimeout(() => {
      this._openind['actionsActive'] = true;
    } , 10);
  }

  /**
   * Set boolean value from custom field
   * @param bool {string}
   * @param action {object}
   */
  public booleanValueCustomField(bool, action) {
    action.value = bool;
    // this.saveKeywordActions();
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
      this._openind['actionsActive'] = false;
      this.listActionCheck = true;
      this.actionCheck = false;
      this.subscriber = [];
      this.customFieldAction = null;
    }
  }

  public checkActions() {
    this.actionsKey.forEach((action, index) => {
      if (!action.hasOwnProperty('id') || !action.id) {
        this.actionsKey.splice(index, 1);
      }
    });
    // this.saveKeywordActions();
  }

  public async saveKeywordActions() {
    this._openind['actionsActive'] = false;
    this.listActionCheck = true;
    this.actionCheck = false;
    this.subscriber = [];
    this.customFieldAction = null;

    const data = {
      actions: this.actionsKey
    };
    try {
      await this._userService.updateKeyword(this._userService.userID, data, this._openind.id);
      this._openind['actions'] = this.actionsKey;
    } catch (err) {
      this._sharedService.showRequestErrors(err);
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
