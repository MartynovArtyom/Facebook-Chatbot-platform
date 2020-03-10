import { Injectable } from '@angular/core';
import { UserService } from './user.service';
import { SharedService } from './shared.service';
import { ToastrService } from 'ngx-toastr';
import { MainMenuItem } from '../../entities/models-user';
import { Router } from '@angular/router';

@Injectable({
  providedIn: 'root'
})
export class MainMenuService {

  public mainData: MainMenuItem[];
  public listMainBreadCrumbs = [];
  public prevData: any;
  public copyright = true;
  public dataPreview = [];
  public levelChildren = 0;
  public movingItemLevel = 0;
  public checkRevert = false;

  constructor(
    private readonly _userService: UserService,
    private readonly _toastr: ToastrService,
    private readonly _router: Router,
    private readonly _sharedService: SharedService
  ) { }

  /**
   * Create new main item
   * @param item {string}
   * @param data {array}
   */
  public createNewMainItem(item, data) {
    if (this._sharedService.prevMainMenuItem) {
      this._sharedService.prevMainMenuItem.close();
    }
    if (item) {
      const res = new MainMenuItem({
        parentID: item
      });
      data.push(res);
    } else {
      const res = new MainMenuItem({});
      data.push(res);
    }

    this.mainData.forEach((datas) => {
      this.traverse(datas, []);
    });

    this.prevData = null;
    this.dataPreview = [];
    this.mainData.forEach((rest) => {
      this.dataPreview.push(rest);
    });
    if (this.copyright) {
      this.dataPreview.push({
        name: 'Unterstützt von ChatBo',
        children: []
      });
    }
    this.updateMainMenuDraft();
  }

  /**
   * Delete item
   * @param data {array}
   * @param i {number}
   */
  public deleteItem(data, i) {
    data.splice(i, 1);
    this.prevData = null;
    this.dataPreview = [];
    this.mainData.forEach((rest) => {
      this.dataPreview.push(rest);
    });
    if (this.copyright) {
      this.dataPreview.push({
        name: 'Unterstützt von ChatBo',
        children: []
      });
    }
    this.updateMainMenuDraft();
  }

  /**
   * Udate main menu draft
   * @returns {Promise<void>}
   */
  public async updateMainMenuDraft(): Promise<void> {
    this._sharedService.savedCheck = false;
    this.checkRevert = true;
    const data = {
      items: this.mainData
    };

    try {
      await this._userService.updateMainMenuDraft(this._userService.userID, data);
      this._sharedService.savedCheck = true;
    } catch (err) {
      this._sharedService.showRequestErrors(err);
    }
  }

  /**
   * Traverse folders bread crumbs
   * @param node {object}
   * @param path {array}
   */
  public traverse(node, path) {
    if (!path) {
      path = [];
    }
    if (node.uuid) {
      path.push({
        uuid: node.uuid,
        name: node.name
      });
    }
    this.listMainBreadCrumbs.push({
      uuid: node.uuid,
      name: node.name,
      breadcrumb: path
    });
  }

  /**
   * Delete flow from main menu item
   * @param item {object}
   */
  public deleteFlowMainMenuItem(item) {
    item.flow = {};
    this.updateMainMenuDraft();
  }

  /**
   * Update type
   * @param type {string}
   * @param item {object}
   */
  public updateType(type, item) {
    item['type'] = type;
    item['children'] = [];
    this.mainData.forEach((datas) => {
      this.traverse(datas, []);
    });

    this.prevData = null;
    this.dataPreview = [];
    this.mainData.forEach((rest) => {
      this.dataPreview.push(rest);
    });
    if (this.copyright) {
      this.dataPreview.push({
        name: 'Unterstützt von ChatBo',
        children: []
      });
    }
    this.updateMainMenuDraft();
  }

  /**
   * Create new flow
   * @param item {object}
   * @returns {Promise<void>}
   */
  public async createNewFlow(item): Promise<any> {
    const data = {
      name: item.name,
      type: 6,
      folderID: null
    };
    try {
      const resp = await this._userService.createFlow(this._userService.userID, data);
      item.flow = resp;
      this.updateMainMenuDraft();
      this.routerToEdit(resp.id);
    } catch (err) {
      this._sharedService.showRequestErrors(err);
    }
  }

  /**
   * Router to edit flow
   * @param id {number}
   */
  public routerToEdit(id) {
    if (this._userService.userPageInfo.role !== 4) {
      this._router.navigate(['/' + this._userService.userID + '/automation/menu/edit/' + id]);
    }
  }

  /**
   * Prev Step message phone preview
   */
  public prevStepMessage() {
    if (this.prevData.breadcrumb.length === 1) {
      this.prevData = null;
      this.dataPreview = [];
      this.mainData.forEach((rest) => {
        this.dataPreview.push(rest);
      });
      if (this.copyright) {
        this.dataPreview.push({
          name: 'Unterstützt von ChatBo',
          children: []
        });
      }
    } else {
      this.listMainBreadCrumbs.forEach((bread) => {
        if (this.prevData.breadcrumb[this.prevData.breadcrumb.length - 2] &&
          bread.uuid === this.prevData.breadcrumb[this.prevData.breadcrumb.length - 2].uuid) {
          this.prevData = bread;
          this.dataPreview = bread.children;
        }
      });
    }
  }

  /**
   * Prev Step message phone preview
   * @param message {object}
   */
  public nextStepMessage(message) {
    this.listMainBreadCrumbs.forEach((bread) => {
      if (message.uuid === bread.uuid) {
        this.prevData = bread;
        this.dataPreview = bread.children;
      }
    });
  }
}
