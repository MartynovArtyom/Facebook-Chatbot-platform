import { Component, Input, OnInit } from '@angular/core';
import { BuilderService } from '../../../../../../modules/messages-builder/services/builder.service';
import { SharedService } from '../../../../../../services/shared.service';
import { AudienceService } from '../../../../../../services/audience.service';
import { NgbModal } from '@ng-bootstrap/ng-bootstrap';
import { BuilderFunctionsService } from '../../../../../../modules/messages-builder/services/builder-functions.service';
import { MainMenuService } from '../../../../../../services/main-menu.service';
import { UserService } from '../../../../../../services/user.service';
import { ToastrService } from 'ngx-toastr';

@Component({
  selector: 'app-main-menu-popover',
  templateUrl: './main-menu-popover.component.html',
  styleUrls: [
    './main-menu-popover.component.scss',
    '../../../../../../../assets/scss/main-menu.scss'
  ]
})
export class MainMenuPopoverComponent implements OnInit {
  public _data: any;
  public _item: any;
  public _index: any;
  public _p: any;
  public actionList = [
    {
      name: 'Add Tag',
      type: 'add_tag'
    },
    {
      name: 'Remove Tag',
      type: 'remove_tag'
    },
    {
      name: 'Subscribe to Sequence',
      type: 'subscribe_sequence'
    },
    {
      name: 'Unsubscribe from Sequence',
      type: 'unsubscribe_sequence'
    },
    {
      name: 'Mark conversation as Open',
      type: 'mark_conversation_open'
    },
    {
      name: 'Notify Admins',
      type: 'notify_admins'
    },
    {
      name: 'Set Custom Field',
      type: 'set_custom_field'
    },
    {
      name: 'Clear subscriber Custom Field',
      type: 'clear_subscriber_custom_field'
    },
    {
      name: 'Subscribe to bot',
      type: 'subscribe_bot'
    },
    {
      name: 'Unsubscribe from bot',
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
  public selectedItem: any;
  public subscriberCount = 0;
  public closeOutside = true;
  public subscriber = [];

  @Input('data') set data(data) {
    if (data) {
      this._data = data;
    }
  }
  get data() {
    return this._data;
  }

  @Input('item') set item(item) {
    if (item) {
      this._item = item;
    }
  }
  get item() {
    return this._item;
  }

  @Input('index') set index(index) {
    if (index) {
      this._index = index;
    }
  }
  get index() {
    return this._index;
  }

  @Input('p') set p(p) {
    if (p) {
      this._p = p;
    }
  }
  get p() {
    return this._p;
  }

  constructor(
    public readonly _sharedService: SharedService,
    public readonly _audienceService: AudienceService,
    private readonly _modalService: NgbModal,
    public readonly _mainMenuService: MainMenuService,
    public readonly _toastr: ToastrService,
    public readonly _builderService: BuilderService,
    public readonly _userService: UserService,
    public readonly _builderFunctionsService: BuilderFunctionsService
  ) {
  }

  ngOnInit() {
  }
  /**
   * Open bulk actions options
   */
  public openActionBulk(keyword) {
    keyword['actionsActive'] = true;
    keyword['actions'].forEach((item) => {
      if (item.type === 'notify_admins') {
        item['openAdmins'] = false;
      }
    });
  }

  /**
   * Choise bulk action
   * @param type {string}
   */
  public choiseBulkAction(type) {
    this._item['actions'].push({
      type: type
    });
    if (type === 'notify_admins') {
      this._item['actions'][this._item['actions'].length - 1]['team'] = [];
    }
    this._mainMenuService.updateMainMenuDraft();
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
      this._mainMenuService.updateMainMenuDraft();
      this._item['actionsActive'] = true;
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
      this._item['actionsActive'] = true;
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
      $event.emoji.native + oField.value.substring(oField.selectionStart, oField.value.length);
    item[key] = oField.value;

    setTimeout(() => {
      this._mainMenuService.updateMainMenuDraft();
      this._item['actionsActive'] = true;
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
    this._mainMenuService.updateMainMenuDraft();

    setTimeout(() => {
      this._item['actionsActive'] = true;
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
    this._mainMenuService.updateMainMenuDraft();
    setTimeout(() => {
      this._item['actionsActive'] = true;
    } , 10);
  }

  /**
   * Create tag
   * @param action {object}
   * @param value {string}
   * @returns {Promise<void>}
   */
  public async createTag(action, value): Promise<any> {
    try {
      const response = await this._userService.createTag(this._userService.userID, value.label);
      this._toastr.success('Tag erstellt');
      this._audienceService.savedTags.conditions.push({
        tagID: response['id'],
        name: response['name']
      });
      action.id = response['id'];
      action.name = response['name'];
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
        this.createTag(action, name);
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
      this._mainMenuService.updateMainMenuDraft();
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
    this._mainMenuService.updateMainMenuDraft();
  }

  /**
   * Set boolean value from custom field
   * @param bool {string}
   * @param action {object}
   */
  public booleanValueCustomField(bool, action) {
    action.value = bool;
    this._mainMenuService.updateMainMenuDraft();
  }

  /**
   * Close tags content
   */
  public onClickedOutside() {
    if (this.closeOutside) {
      this._item['actionsActive'] = false;
      this.listActionCheck = true;
      this.actionCheck = false;
      this.subscriber = [];
      this.customFieldAction = null;
    }
  }

  /**
   * Check actons
   */
  public checkActions() {
    this._item.actions.forEach((action, index) => {
      if (!action.hasOwnProperty('id') || !action.id) {
        this._item.actions.splice(index, 1);
      }
    });
    this._mainMenuService.updateMainMenuDraft();
  }

  /**
   * Open popup
   * @param content {any}
   * @param flow {object}
   */
  public openVerticallyCentered(content, flow) {
    this.selectedItem = flow;
    this.modalActiveClose = this._modalService.open(content, {
      'size': 'lg',
      windowClass: 'flow-popup',
      backdropClass: 'light-blue-backdrop'});
  }
}
