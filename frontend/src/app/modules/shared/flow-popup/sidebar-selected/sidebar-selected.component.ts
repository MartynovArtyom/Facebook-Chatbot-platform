import { Component, Input, OnInit } from '@angular/core';
import { BuilderService } from '../../../messages-builder/services/builder.service';
import { UserService } from '../../../../services/user.service';
import { SharedService } from '../../../../services/shared.service';
import { MainMenuService } from '../../../../services/main-menu.service';
import { Router } from '@angular/router';

@Component({
  selector: 'app-sidebar-selected',
  templateUrl: './sidebar-selected.component.html',
  styleUrls: ['./sidebar-selected.component.scss']
})
export class SidebarSelectedComponent implements OnInit {

  public _data: any;
  public _config: any;
  public _changePreloader: any;

  @Input() closePopup;

  @Input('changePreloader') set changePreloader(changePreloader) {
    this._changePreloader = changePreloader;
  }
  get changePreloader() {
    return this._changePreloader;
  }

  @Input('data') set data(data) {
    if (data) {
      this._data = data;
    }
  }
  get data() {
    return this._data;
  }

  @Input('config') set config(config) {
    if (config) {
      this._config = config;
    }
  }
  get config() {
    return this._config;
  }
  public startStep: any;

  constructor(
    private _builderService: BuilderService,
    private _sharedService: SharedService,
    private _mainMenuService: MainMenuService,
    private _router: Router,
    private _userService: UserService
  ) { }

  ngOnInit() {
    if (this._data.items.length > 0) {
      this._data.items.forEach(item => {
        if (item.start_step) {
          this.startStep = item;
        }
      });
    }
  }

  /**
   * Select flow to content
   * @param data {object}
   */
  public selectFlowToContent(data) {
    if (this._config.hasOwnProperty('name_select_flow')) {
      this._config.id_select_flow = data.id;
      this._config['type_selected_parent_item'] = data.dataParent.type;
      if (data.dataParent.type === 'keywords') {
        this._config.name_select_flow = data.name;
      } else {
        this._config.name_select_flow = data.dataParent.parent.name;
      }
      this._config['id_selected_parent_item'] = data.dataParent.parent.id;
      if (this._config['type_selected_parent_item'] === 'autoresponder') {
        this._config['name_tab'] = data.dataParent.parent.title;
      }
      this._builderService.updateDraftItem();
      this.closePopup();
    } else if (this._config.hasOwnProperty('details')) {
      this.updateWidget(data.id, data.name);
    } else if (this._config.hasOwnProperty('type') && this._config.type === 'default') {
      this.updateDefault(data.id);
    } else if (this._config.hasOwnProperty('type') && this._config.type === 'welcome') {
      this.updateWelcomeMessage(data.id);
    } else if (this._config.hasOwnProperty('type') && this._config.type === 'keyword') {
      this.updateKeyword('flowID', data, this._config);
    } else if (this._config.hasOwnProperty('type') && this._config.type === 'sequence') {
      this.updateSequenceItem(this._config, 'flowID', data);
    } else {
      if (this._config.hasOwnProperty('delay')) {
        this.updateSequenceItem(this._config, 'flowID', data);
      } else if (this._config.hasOwnProperty('type') && this._config.type === 'reply_message') {
        this._config.flow = data;
        this._mainMenuService.updateMainMenuDraft();
        this.closePopup();
      } else {
        this.updateKeyword('flowID', data, this._config);
      }
    }
  }

  /**
   * Update default reply
    * @param id {number}
   * @returns {Promise<void>}
   */
  public async updateDefault(id): Promise<any> {
    const data = {
      flowID: id
    };

    try {
      await this._userService.updateDefaultReply(this._userService.userID, data);
      this._config['flowID'] = id;
      this.closePopup();
      this._router.navigate(['/' + this._userService.userID + '/automation/default']);
    } catch (err) {
      this._sharedService.showRequestErrors(err);
    }
  }

  /**
   * Update default reply
    * @param id {number}
   * @returns {Promise<void>}
   */
  public async updateWelcomeMessage(id): Promise<any> {
    const data = {
      flowID: id
    };

    try {
      await this._userService.updateWelcomeMessage(this._userService.userID, data);
      this._config['flowID'] = id;
      this.closePopup();
    } catch (err) {
      this._sharedService.showRequestErrors(err);
    }
  }

  /**
   * Update default reply
    * @param id {number}
    * @param name {string}
   * @returns {Promise<void>}
   */
  public async updateWidget(id, name): Promise<any> {
    const data = {
      flowID: id
    };

    try {
      await this._userService.updateWidget(this._userService.userID, this._config.details.id, data);
      this._config['details'].flow.id = id;
      this._config['details'].flow.name = name;
      this._changePreloader = false;
      setTimeout(() => {
        this._changePreloader = true;
        this.closePopup();
      }, 10);
    } catch (err) {
      this._sharedService.showRequestErrors(err);
    }
  }

  /**
   * Update sequence item
   * @param item
   * @param key
   * @param obj
   * @returns {Promise<void>}
   */
  public async updateSequenceItem(item, key, obj): Promise<any> {
    const data = {};
    if (key === 'flowID') {
      data[key] = obj.id;
    }
    this._config.flow = obj;

    try {
      await this._userService.updateSequenceItem(this._userService.userID, data, this._sharedService.sequenceId, item.id);
      this._config['flowID'] = obj.id;
      this.closePopup();
    } catch (err) {
      this._sharedService.showRequestErrors(err);
    }
  }

  /**
   * Update keyword
   * @param key {string}
   * @param value {string}
   * @param config {object}
   * @returns {Promise<void>}
   */
  public async updateKeyword(key, value, config) {
    const data = {};
    data[key] = value.id;
    try {
      await this._userService.updateKeyword(this._userService.userID, data, config.id);
      this._config['flowID'] = value.id;
      config.flow = value;
      this.closePopup();
    } catch (err) {
      this._sharedService.showRequestErrors(err);
    }
  }
}
