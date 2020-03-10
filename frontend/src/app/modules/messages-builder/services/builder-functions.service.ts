import { Injectable } from '@angular/core';
import { BuilderService } from './builder.service';
import { UUID } from 'angular2-uuid';
import {
  Action, ActionCustomField, ActionNotifyAdmins, ActionNoun, Arrow,
  AudioChildClass, Button, ButtonClass, CardChildClass, CardClass, Child, ChildClass, ConditionClass, DelayChildClass,
  FileChildClass,
  ImageChildClass, ItemClass, QuickReplyClass, RandomItemClass, RandomizerClass,
  SmartDelayChildClass, StartAnotherFlow,
  TextChildClass, UserInputChildClass, VideoChildClass
} from '../builder/builder-interface';
import { SharedService } from '../../../services/shared.service';
import { ArrowsService } from './arrows.service';
import { ToastrService } from 'ngx-toastr';
import { UserService } from '../../../services/user.service';

@Injectable({
  providedIn: 'root'
})
export class BuilderFunctionsService {

  public uuid = UUID.UUID();
  public openListMainItemsCheck = false;
  public position = 10500;

  constructor(
    private readonly _builderService: BuilderService,
    private readonly _arrowsService: ArrowsService,
    private readonly _sharedService: SharedService,
    private readonly _userService: UserService,
    private readonly _toastr: ToastrService
  ) { }

  /**
   * Delete main items
   * @param config {object}
   */
  public deleteMainItem(config) {
    this._builderService.openSidebar = false;
    this._builderService.requestDataSidebar = {
      type: 'default',
      name: 'Sende Nachricht',
      widget_content: []
    };
    this._builderService.requestDataItems.forEach((data, index) => {
      if (data.uuid === config.uuid && data.start_step) {
        this._toastr.error('Unable to löschen \'Start\' Inhalt, bitte setzen Sie einen anderen Inhalt als \'Start\' bevor Sie es löschen.');
      } else if (data.uuid === config.uuid && !data.start_step) {
        this._builderService.requestDataItems.splice(index, 1);
        this.deleteAllConnectors(config.uuid);
        this._builderService.updateDraftItem();
      }
    });
  }

  /**
   * Delete all connections
   * @param uuid {string}
   */
  public deleteAllConnectors(uuid) {
    this._arrowsService.linksArray.forEach((item, i) => {
      if (item.toArr[0].fromObj.id === uuid) {
        this._arrowsService.linksArray.splice(i, 1);
      } else if (item.toArr[0].toObj.id === uuid) {
        this._builderService.requestDataItems.forEach((data) => {
          if (data.arrow.to.id === uuid) {
            data.next_step = null;
            data.arrow.to = new Arrow({});
            this._arrowsService.linksArray.splice(i, 1);
          }
          this.deleteVlogElem(data, i, uuid);
        });
      }
    });
  }

  /**
   * Delete vlog element
   * @param item {object}
   * @param i {number}
   * @param uuid {string}
   */
  public deleteVlogElem(item, i, uuid) {
    if (item.type === 'send_message') {
      item.widget_content.forEach((data) => {
        if (data.type === 'text' || data.type === 'image' || data.type === 'video' || data.type === 'user_input') {
          data.params.buttons.forEach((button) => {
            if (button.arrow.to.id === uuid) {
              button.next_step = null;
              if (button.type !== 'open_website') {
                button.type = null;
              }
              button.arrow.to = new Arrow({});
              this._arrowsService.linksArray.splice(i, 1);
            }
          });
        } else if (data.type === 'card' || data.type === 'gallery') {
          data.params.cards_array.forEach((card) => {
            card.buttons.forEach((button) => {
              if (button.arrow.to.id === uuid) {
                button.next_step = null;
                if (button.type !== 'open_website') {
                  button.type = null;
                }
                button.arrow.to = new Arrow({});
                this._arrowsService.linksArray.splice(i, 1);
              }
            });
          });
        }
      });

      item.quick_reply.forEach((data) => {
        if (data.buttons[0].arrow.to.id === uuid) {
          data.next_step = null;
          data.type = null;
          if (data.type !== 'open_website') {
            data.type = null;
          }
          data.buttons[0].arrow.to = new Arrow({});
          this._arrowsService.linksArray.splice(i, 1);
        }
      });
    } else if (item.type === 'randomizer') {
      item.widget_content[0].randomData.forEach((data) => {
        if (data.arrow.to.id === uuid) {
          data.next_step = null;
          data.type = null;
          data.arrow.to = new Arrow({});
          this._arrowsService.linksArray.splice(i, 1);
        }
      });
    } else if (item.type === 'condition') {
      if (item.widget_content[0].valid_step.arrow.to.id === uuid) {
        item.widget_content[0].valid_step.next_step = null;
        item.widget_content[0].valid_step.type = null;
        item.widget_content[0].valid_step.arrow.to = new Arrow({});
        this._arrowsService.linksArray.splice(i, 1);
      }
      if (item.widget_content[0].invalid_step.arrow.to.id === uuid) {
        item.widget_content[0].invalid_step.next_step = null;
        item.widget_content[0].invalid_step.type = null;
        item.widget_content[0].invalid_step.arrow.to = new Arrow({});
        this._arrowsService.linksArray.splice(i, 1);
      }
    }
  }

  /**
   * Add item from sidebar
   * @param type {string}
   * @param array {Child[]}
   */
  public addItemFromSidebar(type, array) {
    const data = new ChildClass({
      uuid: UUID.UUID(),
      type: type,
    });

    switch (type) {
      case 'text':
        data.params = new TextChildClass({
          description: '',
          buttons: []
        });
        break;
      case 'image':
        data.params = new ImageChildClass({
          img_url: '',
          buttons: []
        });
        break;
      case 'card':
        data.params = new CardChildClass([
          {
            title: '',
            subtitle: '',
            img_url: '',
            url_page: '',
            active: true,
            activeTitlePanel: false,
            activeSubtitlePanel: false,
            buttons: []
          }
        ]);
        break;
      case 'gallery':
        data.params = new CardChildClass([
          {
            title: '',
            subtitle: '',
            img_url: '',
            url_page: '',
            active: true,
            activeTitlePanel: false,
            activeSubtitlePanel: false,
            buttons: []
          },
          {
            title: '',
            subtitle: '',
            img_url: '',
            url_page: '',
            active: false,
            activeTitlePanel: false,
            activeSubtitlePanel: false,
            buttons: []
          }
        ]);
        break;
      case 'audio':
        data.params = new AudioChildClass({
          name: '',
          url: ''
        });
        break;
      case 'video':
        data.params = new VideoChildClass({
          name: '',
          url: '',
          buttons: []
        });
        break;
      case 'file':
        data.params = new FileChildClass({
          name: '',
          url: ''
        });
        break;
      case 'delay':
        data.params = new DelayChildClass({
          time: 3,
          type_action: true
        });
        break;
      case 'user_input':
        const id = UUID.UUID();
        data.params = new UserInputChildClass({
          buttons: [new ButtonClass({
            uuid: id,
            arrow: {
              from: {
                id: id
              }
            }
          })],
          keyboardInput: {
            replyType: 0,
            response_custom_field: null,
            retry_message: null,
            text_on_button: null,
            skip_button: null,
            active: null,
            id: null
          }
        });
        break;
    }
    array.push(data);

    setTimeout(() => {
      this._builderService.getPointsPosition(this._builderService.config, this._builderService.elem);
      this._builderService.updateDraftItem();
    }, 50);
  }

  /**
   * Delete item from sidebar
   * @param item {object}
   */
  public deleteItemFromSidebar(item) {
    this._builderService.requestDataSidebar.widget_content.forEach((data, index) => {
      if (data.uuid === item.uuid) {
        this.deleteItemsSidebar(item);
        this._builderService.requestDataSidebar.widget_content.splice(index, 1);
        setTimeout(() => {
          this._builderService.getPointsPosition(this._builderService.config, this._builderService.elem);
          this._builderService.updateDraftItem();
        }, 10);
      }
    });
  }

  /**
   * Delete items sidebar
   * @param item {object}
   */
  public deleteItemsSidebar(item) {
    if (item.hasOwnProperty('type')) {
      if (item.type === 'text' || item.type === 'image' || item.type === 'video' || item.type === 'user_input') {
        item.params.buttons.forEach((button) => {
          const index = this._arrowsService.linksArray.findIndex((link) => link.toArr[0].fromObj.id === button.uuid);
          if (index !== -1) {
            this._arrowsService.linksArray.splice(index, 1);
          }
        });
      } else if (item.type === 'card' || item.type === 'gallery') {
        item.params.cards_array.forEach((card) => {
          card.buttons.forEach(button => {
            const index = this._arrowsService.linksArray.findIndex((link) => link.toArr[0].fromObj.id === button.uuid);
            if (index !== -1) {
              this._arrowsService.linksArray.splice(index, 1);
            }
          });
        });
      }
    }
  }

  /**
   * Delete quick reply from sidebar
   * @param items {array}
   * @param index {number}
   */
  public deleteQuickReply(items, index) {
    const i = this._arrowsService.linksArray.findIndex(link => link.toArr[0].fromObj.id === items[index].uuid);
    if (i !== -1) {
      this._arrowsService.linksArray.splice(i, 1);
    }
    items.splice(index, 1);
    setTimeout(() => {
      this._builderService.getPointsPosition(this._builderService.config, this._builderService.elem);
      this._builderService.updateDraftItem();
    }, 10);
  }

  /**
   * Clone item from sidebar
   * @param item {object}
   */
  public cloneItemFromSidebar(item) {
    const data = this.cloneObjects(item);
    this._builderService.requestDataSidebar.widget_content.push(data);
  }

  /**
   * Clone sidebar items
   * @param obj {object}
   * @returns {any}
   */
  public cloneObjects(obj: any) {
    const data = JSON.parse(JSON.stringify(obj));

    switch (data.type) {
      case 'text':
        data.uuid = UUID.UUID();
        this.resetButtonUUID(data.params.buttons);
        break;
      case 'image':
        data.uuid = UUID.UUID();
        this.resetButtonUUID(data.params.buttons);
        break;
      case 'card':
        data.uuid = UUID.UUID();
        data.params.cards_array.forEach((card) => {
          this.resetButtonUUID(card.buttons);
        });
        break;
      case 'gallery':
        data.uuid = UUID.UUID();
        data.params.cards_array.forEach((card) => {
          this.resetButtonUUID(card.buttons);
        });
        break;
      case 'audio':
        data.uuid = UUID.UUID();
        break;
      case 'video':
        data.uuid = UUID.UUID();
        this.resetButtonUUID(data.params.buttons);
        break;
      case 'file':
        data.uuid = UUID.UUID();
        break;
      case 'delay':
        data.uuid = UUID.UUID();
        break;
      case 'user_input':
        data.uuid = UUID.UUID();
        data.params.id_link = UUID.UUID();
        this.resetButtonUUID(data.params.buttons);
        data.params.quick_reply.forEach((quick) => {
          quick.uuid = UUID.UUID();
          data.activeTitlePanel = false;
        });
        break;
    }

    setTimeout(() => {
      this._builderService.getPointsPosition(this._builderService.config, this._builderService.elem);
      this._builderService.updateDraftItem();
    }, 10);
    return data;
  }

  /**
   * Clone main items
   * @param config {object}
   */
  public cloneMainItems(config) {
    switch (config.type) {
      case 'send_message':
        this.cloneSendMessage(config);
        break;
      case 'perform_actions':
        this.clonePerformActions(config);
        break;
      case 'start_another_flow':
        this.cloneStartAnotherFlow(config);
        break;
      case 'condition':
        this.cloneCondition(config);
        break;
      case 'randomizer':
        this.cloneRandomizer(config);
        break;
      case 'smart_delay':
        this.cloneSmartDelay(config);
        break;
    }
  }

  /**
   * Clone send message main item
   * @param config {object}
   */
  public cloneSendMessage(config) {
    const data = JSON.parse(JSON.stringify(config));
    const id = UUID.UUID();
    data['id'] = data['id'] + 1;
    data['uuid'] = id;
    data['start_step'] = false;
    data['x'] = data['x'] + 20;
    data['y'] = data['y'] + 20;
    data['next_step'] = null;
    data.arrow.to = new Arrow({});
    data.arrow.from = new Arrow({
      id: id,
      fromItemX: data['x'] + 20 + 400,
      fromItemY: data['y'] + 20,
    });

    data.widget_content.forEach((item) => {
      switch (item.type) {
        case 'text':
          item.uuid = UUID.UUID();
          this.resetButtonUUID(item.params.buttons);
          break;
        case 'image':
          item.uuid = UUID.UUID();
          this.resetButtonUUID(item.params.buttons);
          break;
        case 'card':
          item.uuid = UUID.UUID();
          item.params.cards_array.forEach((card) => {
            this.resetButtonUUID(card.buttons);
          });
          break;
        case 'gallery':
          item.uuid = UUID.UUID();
          item.params.cards_array.forEach((card) => {
            this.resetButtonUUID(card.buttons);
          });
          break;
        case 'audio':
          item.uuid = UUID.UUID();
          break;
        case 'video':
          item.uuid = UUID.UUID();
          this.resetButtonUUID(item.params.buttons);
          break;
        case 'file':
          item.uuid = UUID.UUID();
          break;
        case 'delay':
          item.uuid = UUID.UUID();
          break;
        case 'user_input':
          item.uuid = UUID.UUID();
          item.params.id_link = UUID.UUID();
          this.resetButtonUUID(item.params.buttons);
          item.params.quick_reply.forEach((quick) => {
            quick.uuid = UUID.UUID();
            item.activeTitlePanel = false;
          });
          break;
      }
    });

    data.quick_reply.forEach((item) => {
      item.uuid = UUID.UUID();
      item.activeTitlePanel = false;
      item.next_step = null;
      this.resetButtonUUID(item.buttons);
    });

    this._builderService.requestDataItems.push(data);
    this._builderService.updateDraftItem();
  }

  /**
   * Reset button uuid and next step
   * @param buttons {array}
   */
  public resetButtonUUID(buttons) {
    buttons.forEach((button) => {
      const id = UUID.UUID();
      button.uuid = id;
      button.next_step = null;
      if (button.type !== 'open_website' && button.type !== 'call_number') {
        button.type = null;
      }
      button.arrow.to = new Arrow({});
      button.arrow.from = new Arrow({
        id: id
      });
    });
  }

  /**
   * Clone perform actions main item
   * @param config {object}
   */
  public clonePerformActions(config) {
    const data = JSON.parse(JSON.stringify(config));

    const id = UUID.UUID();
    data['id'] = data['id'] + 1;
    data['uuid'] = id;
    data['start_step'] = false;
    data['x'] = data['x'] + 20;
    data['y'] = data['y'] + 20;
    data['next_step'] = null;
    data.arrow.to = new Arrow({});
    data.arrow.from = new Arrow({
      id: id,
      fromItemX: data['x'] + 20 + 400,
      fromItemY: data['y'] + 20,
    });

    this._builderService.requestDataItems.push(data);
    this._builderService.updateDraftItem();
  }

  /**
   * Clone start another flow main item
   * @param config {object}
   */
  public cloneStartAnotherFlow(config) {
    const data = JSON.parse(JSON.stringify(config));

    const id = UUID.UUID();
    data['id'] = data['id'] + 1;
    data['uuid'] = id;
    data['start_step'] = false;
    data['x'] = data['x'] + 20;
    data['y'] = data['y'] + 20;
    data['next_step'] = null;
    data.arrow.to = new Arrow({});
    data.arrow.from = new Arrow({
      id: id,
      fromItemX: data['x'] + 20 + 400,
      fromItemY: data['y'] + 20,
    });

    this._builderService.requestDataItems.push(data);
    this._builderService.updateDraftItem();
  }

  /**
   * Clone condition main item
   * @param config {object}
   */
  public cloneCondition(config) {
    const data = JSON.parse(JSON.stringify(config));

    data['id'] = data['id'] + 1;
    data['uuid'] = UUID.UUID();
    data['start_step'] = false;
    data['x'] = data['x'] + 20;
    data['y'] = data['y'] + 20;
    data['next_step'] = null;

    const id1 = UUID.UUID();
    data.widget_content[0].valid_step.arrow.to = new Arrow({});
    data.widget_content[0].valid_step.arrow.from = new Arrow({
      id: id1
    });
    data.widget_content[0].valid_step.next_step = null;
    data.widget_content[0].valid_step.uuid = id1;

    const id2 = UUID.UUID();
    data.widget_content[0].invalid_step.arrow.to = new Arrow({});
    data.widget_content[0].invalid_step.arrow.from = new Arrow({
      id: id2
    });
    data.widget_content[0].invalid_step.next_step = null;
    data.widget_content[0].invalid_step.uuid = id2;

    this._builderService.requestDataItems.push(data);
    this._builderService.updateDraftItem();
  }

  /**
   * Clone randomizer main item
   * @param config {object}
   */
  public cloneRandomizer(config) {
    const data = JSON.parse(JSON.stringify(config));

    data['id'] = data['id'] + 1;
    data['uuid'] = UUID.UUID();
    data['start_step'] = false;
    data['x'] = data['x'] + 20;
    data['y'] = data['y'] + 20;

    data.widget_content[0].randomData.forEach((item) => {
      const id = UUID.UUID();
      item.uuid = id;
      item.next_step = null;
      item.arrow.to = new Arrow({});
      item.arrow.from = new Arrow({
        id: id
      });
    });

    this._builderService.requestDataItems.push(data);
    this._builderService.updateDraftItem();
  }

  /**
   * Clone smart delay main item
   * @param config {object}
   */
  public cloneSmartDelay(config) {
    const data = JSON.parse(JSON.stringify(config));

    const id = UUID.UUID();
    data['id'] = data['id'] + 1;
    data['uuid'] = id;
    data['start_step'] = false;
    data['x'] = data['x'] + 20;
    data['y'] = data['y'] + 20;
    data['next_step'] = null;
    data.arrow.to = new Arrow({});
    data.arrow.from = new Arrow({
      id: id,
      fromItemX: data['x'] + 20 + 400,
      fromItemY: data['y'] + 20,
    });

    data.widget_content.forEach((item) => {
      item.uuid = UUID.UUID();
    });

    this._builderService.requestDataItems.push(data);
    this._builderService.updateDraftItem();
  }

  /**
   * Request data to sidebar
   * @param item {object}
   */
  public requestDataToSidebar(item) {
    this._builderService.checkOpenSidebar = false;
    if (this._builderService.counter === 0 && !this._builderService.statusCreateLink) {
      this._builderService.openSidebar = true;
    } else if (this._builderService.counter === 1 && !this._builderService.statusCreateLink) {
      this._builderService.openSidebar = true;
    }
    if (this._builderService.requestDataSidebar['uuid'] !== item.uuid) {
      this._builderService.requestDataSidebar = item;
    }
  }

  /**
   * Set input value to object
   * @param object {object)
   * @param key {string}
   * @param value {string}
   */
  public setInputValue(object, key, value) {
    object[key] = value;
    this._builderService.updateDraftItem();
  }

  /**
   * Upload file from send message type widget
   * @param item {object}
   * @param files {file}
   */
  public async uploadFile(item, files): Promise<any> {
    if (files.length > 0) {
      const formData = new FormData();
      formData.append('file', files[0]);

      try {
        const response = await this._userService.uploadFileFlowItem(this._userService.userID ,
          this._builderService.requestDataAll.id, formData);
        item['img_url'] = response.url;
      } catch (err) {
        this._sharedService.showRequestErrors(err);
      }
    }
    setTimeout(() => {
      this._builderService.getPointsPosition(this._builderService.config, this._builderService.elem);
      this._builderService.updateDraftItem();
    }, 10);
  }

  /**
   * Upload file from send message type widget
   * @param item {object}
   * @param files {file}
   * @param type {string}
   */
  public async uploadVideoFile(item, files, type): Promise<any> {
    if (files.length > 0) {
      const formData = new FormData();
      formData.append('file', files[0]);
      if (!type) {
        formData.append('type', null);
      } else {
        formData.append('type', type);
      }

      try {
        const response = await this._userService.uploadFileFlowItem(this._userService.userID ,
          this._builderService.requestDataAll.id, formData);
        item['url'] = response.url;
        item['name'] = response.name;
      } catch (err) {
        this._sharedService.showRequestErrors(err);
      }
    }
    this._builderService.updateDraftItem();
  }

  /**
   * Prev card from card array item
   * @param array {array}
   * @param index {number}
   */
  public prevCardItem(array, index) {
    array[index].active = false;
    array[index - 1].active = true;
  }

  /**
   * Next card from card array item
   * @param array {array}
   * @param index {number}
   */
  public nextCardItem(array, index) {
    array[index].active = false;
    array[index + 1].active = true;
  }

  /**
   * Create card from card array item
   * @param array {array}
   * @param index {number}
   */
  public createNewCardItem(array, index) {
    array[index].active = false;
    array.push(new CardClass({}));
    this._builderService.updateDraftItem();
  }

  /**
   * Delete card from card array item
   * @param array {array}
   * @param index {number}
   * @param item {object}
   */
  public deleteCardItem(array, index, item) {
    this.deleteItemsSidebar(array[index]);
    if (array.length - 1 === index && array.length !== 1) {
      array[index - 1].active = true;
      array.splice(index, 1);
    } else if (array.length === 1) {
      this.deleteItemFromSidebar(item);
    } else if (array.length - 1 !== index) {
      array[index + 1].active = true;
      array.splice(index, 1);
    }
    setTimeout(() => {
      this._builderService.getPointsPosition(this._builderService.config, this._builderService.elem);
      this._builderService.updateDraftItem();
    }, 10);
  }

  /**
   * Delete image from card item
   * @param card {object}
   */
  public deleteImageCardItem(card) {
    card.img_url = null;
    this._builderService.updateDraftItem();
  }

  /**
   * Open sub panel text area
   * @param card {object}
   * @param key {string}
   */
  public openSubPanel(card, key) {
    card[key] = true;
  }

  /**
   * Close sub panel text area
   * @param card {object}
   * @param key {string}
   */
  public closeSubPanel(card, key) {
    card[key] = false;
  }

  /**
   *
   * @param action
   * @param item
   * @param type
   */
  public choiseDelayTime(action, item) {
    if (action === 'plus') {
      if (item.time >= 20) {
        item.time = 20;
      } else {
        item.time++;
      }
    } else if (action === 'minus') {
      if (item.time <= 1) {
        item.time = 1;
      } else {
        item.time--;
      }
    } else {
      if (item.type_action) {
        if (item.type_action === 1) {
          if (action >= 60) {
            item.time = 60;
          } else if (action <= 1) {
            item.time = 1;
          } else {
            item.time = action;
          }
        } else if (item.type_action === 2) {
          if (action >= 24) {
            item.time = 24;
          } else if (action <= 1) {
            item.time = 1;
          } else {
            item.time = action;
          }
        } else if (item.type_action === 3) {
          if (action >= 30) {
            item.time = 30;
          } else if (action <= 1) {
            item.time = 1;
          } else {
            item.time = action;
          }
        } else {
          if (action >= 20) {
            item.time = 20;
          } else if (action <= 1) {
            item.time = 1;
          } else {
            item.time = action;
          }
        }
      } else {
        if (action >= 20) {
          item.time = 20;
        } else if (action <= 1) {
          item.time = 1;
        } else {
          item.time = action;
        }
      }
    }
    this._builderService.updateDraftItem();
  }

  /**
   * Switch from delay type widget
   */
  public checkDelayShow(item) {
    item.type_action = !item.type_action;
    this._builderService.updateDraftItem();
  }

  /**
   * Create new quick reply item to quick reply array
   * @param array {array}
   */
  public createNewQuickReply(array) {
    const id = UUID.UUID();
    array.push(new QuickReplyClass({
      uuid: id,
      buttons: [new ButtonClass({
        arrow: {
          from: {
            id: id,
            fromItemX: 500,
            fromItemY: 500
          }
        }
      })]
    }));
    setTimeout(() => {
      this._builderService.getPointsPosition(this._builderService.config, this._builderService.elem);
      this._builderService.updateDraftItem();
    }, 20);
  }

  /**
   * Create new quick reply item to user input array
   * @param array {array}
   */
  public createNewQuickReplyUserInput(array) {
    array.push(new QuickReplyClass({}));
    setTimeout(() => {
      this._builderService.getPointsPosition(this._builderService.config, this._builderService.elem);
      this._builderService.updateDraftItem();
    }, 10);
  }

  /**
   * Set value to user input fields
   * @param field {object}
   * @param key {string}
   * @param value {string}
   */
  public setValueToInputField(field, key, value) {
    field[key] = value;
    this._builderService.updateDraftItem();
  }

  /**
   * Set value to user input fields
   * @param field {object}
   * @param value {string}
   */
  public setValueToInputFieldCF(field, value) {
    if (value) {
      field['response_custom_field'] = value['name'];
      field['id'] = value['customFieldID'];
    } else {
      field['response_custom_field'] = null;
      field['id'] = null;
    }
    this._builderService.updateDraftItem();
  }

  /**
   * Open list main items created
   * @param value {string}
   */
  public openListMainItems(value) {
    this.openListMainItemsCheck = value;
  }

  /**
   * Create main item component
   * @param type {string}
   * @param name {string}
   * @param status {string}
   */
  public createMainItemComponent(type, name, status: string = 'all') {
    this.openListMainItemsCheck = false;
    const dataId = UUID.UUID();
    const data = new ItemClass({
      id: 1,
      hideNextStep: (status === 'all') ? false : true,
      uuid: dataId,
      arrow: {
        to: new Arrow({}),
        from: new Arrow({
          id: dataId,
          fromItemX: this.position + 400,
          fromItemY: this.position
        })
      },
      type: type,
      name: name,
      next_step: null,
      start_step: false,
      widget_content: [],
      quick_reply: [],
      x: this.position,
      y: this.position
    });

    this.switchMainData(type, data.widget_content);

    this._builderService.requestDataItems.push(data);
    return data;
  }

  /**
   * Switch main data
   * @param type {string}
   * @param data {array}
   */
  public switchMainData(type, data) {
    switch (type) {
      case 'send_message':
        data.push(new ChildClass({
          uuid: UUID.UUID(),
          type: 'text',
          params: new TextChildClass({})
        }));
        break;
      case 'condition':
        const i1 = UUID.UUID();
        const i2 = UUID.UUID();
        data.push(new ConditionClass({
          valid_step: {
            uuid: i1,
            arrow: {
              from: new Arrow({
                id: i1
              }),
              to: new Arrow({})
            }
          },
          invalid_step: {
            uuid: i2,
            arrow: {
              from: new Arrow({
                id: i2
              }),
              to: new Arrow({})
            }
          }
        }));
        break;
      case 'start_another_flow':
        data.push(new StartAnotherFlow({}));
        break;
      case 'randomizer':
        const id1 = UUID.UUID();
        const id2 = UUID.UUID();
        data.push(new RandomizerClass({
          randomData: [new RandomItemClass({
            random_leter: 'A',
            uuid: id1,
            arrow: {
              from: new Arrow({
                id: id1
              }),
              to: new Arrow({})
            }
          }),
            new RandomItemClass({
              random_leter: 'B',
              uuid: id2,
              arrow: {
                from: new Arrow({
                  id: id2
                }),
                to: new Arrow({})
              }
            })]
        }));
        break;
      case 'smart_delay':
        data.push(new SmartDelayChildClass({}));
        break;
    }
  }

  /**
   * Create new main item from button
   * @param button {object}
   * @param type {string}
   * @param name {string}
   * @param status {string}
   */
  public createNewMainFromButton(button, type: string, name: string, status: string = 'all') {
    button.btnValue = null;
    if (type !== 'open_website' && type !== 'call_number') {
      const data = this.createMainItemComponent(type, name, status);
      if (button.hasOwnProperty('type_next_step')) {
      } else if (!button.type) {
          button.type = type;
      }
      button.next_step = this._builderService.requestDataItems[this._builderService.requestDataItems.length - 1].uuid;
      button.arrow.to.id = button.next_step;
      button.arrow.to.toItemX = data.x;
      button.arrow.to.toItemY = data.y;
      button.arrow.from.id = button.uuid;
      if (button.hasOwnProperty('x') && button.hasOwnProperty('y')) {
        button.arrow.from.fromItemX = button.x + 400;
        button.arrow.from.fromItemY = button.y;
      }
    } else {
      button.type = type;
    }
    this._builderService.updateDraftItem();
  }

  /**
   * Create new main item from quick reply
   * @param button {object}
   * @param type {string}
   * @param name {string}
   */
  public createNewMainFromQuickReply(button, type: string, name: string) {
    const data = this.createMainItemComponent(type, name);
    button.type = type;

    button.next_step = this._builderService.requestDataItems[this._builderService.requestDataItems.length - 1].uuid;
    button.buttons[0].arrow.to.id = button.next_step;
    button.buttons[0].arrow.to.toItemX = data.x;
    button.buttons[0].arrow.to.toItemY = data.y;
    button.buttons[0].arrow.from.id = button.uuid;

    this._builderService.updateDraftItem();
  }

  /**
   * Review open website type button
   * @param id {string}
   * @returns {number}
   */
  public reviewOpenWebsite(id) {
    let count = 0;
    this._builderService.requestDataItems.forEach((data) => {
      if (data.type === 'send_message') {
        data.widget_content.forEach(item => {
          if (item.type === 'text' || item.type === 'image' || item.type === 'video') {
            item.params.buttons.forEach((button) => {
              if (button.next_step === id && button.type === 'open_website') {
                count++;
              }
            });
          } else if (item.type === 'card' || item.type === 'gallery') {
            item.params.cards_array.forEach((card) => {
              card.buttons.forEach((button) => {
                if (button.next_step === id && button.type === 'open_website') {
                  count++;
                }
              });
            });
          }
        });
      }
    });
    return count;
  }

  /**
   * Check Perform Action next step
   * @param next_step {string}
   */
  public checkPANextStep(next_step) {
    let counter = 0;
    this._arrowsService.linksArray.forEach((data) => {
      if (next_step === data.toArr[0].toObj.id) {
        counter++;
      }
    });

    if (counter === 1) {
      const d = this._builderService.requestDataItems.find(el => el.uuid === next_step);
      d.hideNextStep = false;
    } else {
      const num = this.reviewOpenWebsite(next_step);
      if (num === 1) {
        const d = this._builderService.requestDataItems.find(el => el.uuid === next_step);
        d.hideNextStep = false;
      }
    }
  }

  /**
   * Delete next step
   * @param item {Button}
   */
  public deleteNextStep(item: Button) {
    if (item.next_step) {
      this.checkPANextStep(item.next_step);
    }
    item.next_step = null;
    if (!item.hasOwnProperty('widget_content')) {
      if (item.type !== 'open_website') {
        item.type = null;
      }
    }

    this._arrowsService.linksArray.forEach((link, i) => {
      if (link.toArr[0].fromObj.id === item.uuid) {
        this._arrowsService.linksArray.splice(i, 1);
      }
    });
    if (item.hasOwnProperty('answer_check')) {
      item['buttons'][0].arrow.to = new Arrow({});
    } else {
      item.arrow.to = new Arrow({});
    }
    setTimeout(() => {
      this._builderService.getPointsPosition(this._builderService.config, this._builderService.elem);
      this._builderService.updateDraftItem();
    }, 10);
  }

  /**
   * Delete type button
   * @param button {object}
   */
  public deleteTypeButton(button) {
    if (button.next_step) {
      this.checkPANextStep(button.next_step);
    }

    button.type = null;
    button.btnValue = null;
  }

  /**
   * Delete button
   * @param array {array}
   * @param i {number}
   */
  public deleteButton(array: Button[], i: number) {
    this.deleteNextStep(array[i]);
    const item = array.splice(i, 1);
    this._builderService.updateDraftItem();
  }

  /**
   * Transition to next step item
   * @param next_step {string}
   */
  public transitionToNextStep(next_step) {
    if (next_step !== 'open_website' || next_step !== 'call_number') {
      this._builderService.requestDataItems.forEach((item) => {
        if (item.uuid === next_step) {
          this._builderService.requestDataSidebar = item;
        }
      });
    }
  }

  /**
   * Create new action to Perform Actions main
   * @param action {object}
   * @param data {object}
   */
  public createNewAction(action, data) {
    if (action.type === 'add_tag' ||
        action.type === 'remove_tag' ||
        action.type === 'subscribe_sequence' ||
        action.type === 'unsubscribe_sequence' ||
        action.type === 'clear_subscriber_custom_field') {
      data.push(new Action({
        type: action.type
      }));
    } else if (action.type === 'mark_conversation_open' ||
               action.type === 'subscribe_bot' ||
               action.type === 'unsubscribe_bot') {
      data.push(new ActionNoun({
        type: action.type
      }));
    } else if (action.type === 'set_custom_field') {
      data.push(new ActionCustomField({
        type: action.type
      }));
    } else if (action.type === 'notify_admins') {
      data.push(new ActionNotifyAdmins({
        type: action.type
      }));
    }
    this._builderService.updateDraftItem();
  }

  /**
   * Delete perform action
   * @param config {array}
   * @param i {number}
   */
  public deleteAction(config, i) {
    if (config.length === 2) {
      this._toastr.error('Muss mindestens 2 Optionen haben');
    } else {
      config.splice(i , 1);
      setTimeout(() => {
        this._builderService.getPointsPosition(this._builderService.config, this._builderService.elem);
        this._builderService.updateDraftItem();
      }, 10);
    }
  }

  /**
   * Delete perform action
   * @param config {array}
   * @param i {number}
   */
  public deleteActions(config, i) {
    config.splice(i , 1);
    setTimeout(() => {
      this._builderService.getPointsPosition(this._builderService.config, this._builderService.elem);
      this._builderService.updateDraftItem();
    }, 10);
  }

  /**
   * Choice admin action status
   * @param admin {object}
   * @param team {array}
   */
  public choiceAdminActionStatus(admin, team) {

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
    this._builderService.updateDraftItem();
  }

  /**
   * Choice random status
   * @param config {object}
   */
  public choiceRandomStatus(config) {
    config.randomCheck = !config.randomCheck;
    this._builderService.updateDraftItem();
  }

  /**
   * Change smart delay type
   * @param value {string}
   * @param item {object}
   */
  public changeSmartDelayType(value, item) {
    item.type_action = Number(value);
    if (item.type_action) {
      if (item.type_action === 1) {
        if (item.time >= 60) {
          item.time = 60;
        } else if (item.time <= 1) {
          item.time = 1;
        }
      } else if (item.type_action === 2) {
        if (item.time >= 24) {
          item.time = 24;
        } else if (item.time <= 1) {
          item.time = 1;
        }
      } else if (item.type_action === 3) {
        if (item.time >= 30) {
          item.time = 30;
        } else if (item.time <= 1) {
          item.time = 1;
        }
      }
    }
    this._builderService.updateDraftItem();
  }

  /**
   * Create new random variator
   * @param data {array}
   */
  public createNewRandomVariation(data) {
    const randomLetter = ['A', 'B', 'C', 'D', 'E', 'F'];
    const dataLetter = [];
    const letterCreate = [];
    data.forEach((item) => {
      dataLetter.push(item.random_leter);
    });

    randomLetter.forEach((letter, i) => {
      const lt = dataLetter.indexOf(letter);
      if (lt === -1) {
        letterCreate.push({
          name: letter,
          index: i
        });
      }
    });

    const nextData = data.splice(letterCreate[0].index);
    const i1 = UUID.UUID();
    data.push(new RandomItemClass({
      uuid: i1,
      value: 50,
      random_leter: letterCreate[0].name,
      arrow: {
        from: new Arrow({
          id: i1
        }),
        to: new Arrow({})
      }
    }));

    nextData.forEach((item) => {
      data.push(item);
    });
    setTimeout(() => {
      this._builderService.getPointsPosition(this._builderService.config, this._builderService.elem);
      this._builderService.updateDraftItem();
    }, 10);
  }

  /**
   * Set starting step
   * @param config {object}
   */
  public setStartingStep(config) {
    this._builderService.requestDataItems.forEach((data) => {
      data.start_step = false;
    });
    config.start_step = true;
    this._builderService.updateDraftItem();
  }

  /**
   * Select following condition value
   * @param value {string}
   * @param data {object}
   */
  public selectFollowingConditionValue(value, data) {
    data['following_conditions'] = value;
    this._builderService.updateDraftItem();
  }

  /**
   * Add emoji
   * @param oField {object}
   * @param $event {event}
   * @param item {object}
   * @param key {string}
   */
  public addEmoji(oField, $event, item, key) {
    if (!oField.value || !item[key]) {
      oField.value = this.fancyCount2('' + $event + '');
    } else {
      oField.value = oField.value.substring(0, oField.selectionStart) +
        this.fancyCount2('' + $event + '') + oField.value.substring(oField.selectionStart, oField.value.length);
    }
    item[key] = oField.value;
    this._builderService.updateDraftItem();
  }


  public fancyCount2(str) {
    const joiner = '\u{200D}';
    const split = str.split(joiner);
    let count = 0;

    for (const s of split) {
      const num = Array.from(s.split(/[\ufe00-\ufe0f]/).join('')).length;
      count += num;
    }

    return split;
  }

  /**
   * Close emoji
   * @param item {object}
   */
  public closeEmoji(item) {
    item = !item;
  }

  /**
   * Open subpanel for textarea panel
   * @param elem {event}
   * @param item {object}
   * @param key {string}
   */
  public openSubTextareaPanel(elem, item, key) {
    elem.focus();
    item[key] = true;
  }

  /**
   * Close subpanel textarea
   * @param item {object}
   * @param key {string}
   * @param actionKey {string}
   * @param actionEmoji {string}
   */
  public closeSubTextareaPanel(item, key, actionKey, actionEmoji) {
    item[key] = false;
    delete item[actionKey];
    delete item[actionEmoji];
  }

  /**
   * Open emoji panel
   * @param value {string}
   * @param item {object}
   * @param key {string}
   * @param actionKey {string}
   */
  public openEmojiPanel(value, item, key, actionKey) {
    item[key] = value;
    delete item[actionKey];
  }

  /**
   * Open bulk action panel
   * @param value {string}
   * @param elem {object}
   * @param item {object}
   * @param key {string}
   * @param actionKey {string}
   */
  public openBulkActionsPanel(value, elem, item, key, actionKey) {
    elem.focus();
    item[key] = value;
    delete item[actionKey];
  }

  /**
   * Get current position curret
   * @param oField {object}
   * @param action {object}
   * @param elem {object}
   * @param item {object}
   * @param key {string}
   */
  public getCaretPos(oField, action, elem, item, key) {
    if (!elem.value || !item[key]) {
      elem.value = action.desc;
    } else {
      elem.value = elem.value.substring(0, oField.selectionStart) +
        action.desc + elem.value.substring(oField.selectionStart, elem.value.length);
    }

    item[key] = elem.value;
    this.closeSubTextareaPanel(item, 'activeTitlePanel', 'activeEmojiTitle', 'activeActionTitle');
    this.closeSubTextareaPanel(item, 'activeSubtitlePanel', 'activeEmojiSubtitle', 'activeActionSubtitle');
    setTimeout(() => {
      elem.blur();
    }, 10);
    this._builderService.updateDraftItem();
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

    this.closeSubTextareaPanelAction(item);
    setTimeout(() => {
      elem.blur();
    }, 10);
    this._builderService.updateDraftItem();
  }

  /**
   * Close sub textarea panel action
   * @param item {object}
   */
  public closeSubTextareaPanelAction(item) {
    item['activeTitlePanel'] = false;
    item['activeEmojiTitle'] = false;
    item['activeActionTitle'] = false;
  }
}
