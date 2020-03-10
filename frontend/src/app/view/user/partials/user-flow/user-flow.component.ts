import { Component, Input, OnInit } from '@angular/core';
import { NgbModal } from '@ng-bootstrap/ng-bootstrap';
import { ActivatedRoute, Router } from '@angular/router';
import { UserService } from '../../../../services/user.service';
import { SharedService } from '../../../../services/shared.service';
import { FlowFoldersService } from '../../../../services/flow-folders.service';
import { ToastrService } from 'ngx-toastr';

@Component({
  selector: 'app-user-flow',
  templateUrl: './user-flow.component.html',
  styleUrls: ['./user-flow.component.scss']
})
export class UserFlowComponent implements OnInit {
  public _sendPopup: boolean;

  @Input() closePopup;
  @Input('sendPopup') set sendPopup(sendPopup) {
    if (sendPopup) {
      this._sendPopup = sendPopup;
    }
  }
  get sendPopup() {
    return this._sendPopup;
  }

  public modalActiveClose: any;
  public rightSidebarPreview = false;
  public createParentFolderName: string;
  public dataPreviewSidebar = {};
  public flowContant: any;
  public folderContant: any;
  public accessRouterCheck = false;

  constructor(
    private readonly _modalService: NgbModal,
    private readonly _router: Router,
    private readonly _sharedService: SharedService,
    public readonly _flowFoldersService: FlowFoldersService,
    public readonly _toastr: ToastrService,
    public readonly _route: ActivatedRoute,
    public readonly _userService: UserService
  ) {
  }

  ngOnInit() {
    this._flowFoldersService.folderPreload = true;
    this.getFolders().then(() => {
      this.setPath();
    });
  }

  /**
   * Route search
   * @param value {string}
   */
  public routeSearch(value) {
    this._router.navigate([this._userService.userID + '/content'], {queryParams: {search: value}});
  }

  /**
   * Get function flows
   */
  public setPath() {
    this._route.queryParams.subscribe(params => {
      if (params['search']) {
        if (params['search'] === '') {
          this._flowFoldersService.getFlowsById('');
        } else {
          this._flowFoldersService.searchFlowsById(params['search']);
        }
      } else if (!params['path']) {
        this._flowFoldersService.getFlowsById('');
      } else {
        this.getNounsFlowsItems(params['path']);
      }
    });
  }

  /**
   * Get nouns url flows items
   * @param link {string}
   */
  public getNounsFlowsItems(link) {
    switch (link) {
      case 'sequences':
        this._flowFoldersService.getSequence();
        break;
      case 'default_reply':
        link = 2;
        this._flowFoldersService.getFlowsByType(link, '');
        break;
      case 'welcome_message':
        link = 3;
        this._flowFoldersService.getFlowsByType(link, '');
        break;
      case 'keywords':
        link = 4;
        this._flowFoldersService.getFlowsByType(link, '');
        break;
      case 'menu':
        link = 6;
        this._flowFoldersService.getFlowsByType(link, '');
        break;
      case 'widget':
        link = 7;
        this._flowFoldersService.getFlowsByType(link, '');
        break;
      case 'trash':
        this._flowFoldersService.getFlowsTrash();
        break;
      default:
        const param = link.split(':');
        if (param[0] === 'sequences') {
          const id = Number(param[1]);
          this._flowFoldersService.getSequenceById(id, param[2]);
        } else {
          this._flowFoldersService.getFlowsById(param[param.length - 1]);
        }
    }
  }

  /**
   * Get folders
   * @returns {Promise<void>}
   */
  public async getFolders(): Promise<any> {
    try {
      this._flowFoldersService.flowFolders = await this._userService.getFolders(this._userService.userID);
      this._flowFoldersService.listBreadCrumb = [];
      this._flowFoldersService.breadCrumb = [];
      this._flowFoldersService.flowFolders.forEach((data) => {
        this._flowFoldersService.traverse(data, []);
      });
      this._flowFoldersService.folderPreload = false;
    } catch (err) {
      this._sharedService.showRequestErrors(err);
    }
  }

  /**
   * Create new flow
   * @param value {string}
   */
  public async createFlow(value): Promise<any> {
    const data = {
      name: value,
      type: 1,
      folderID: this._flowFoldersService.folderID
    };

    try {
      const response = await this._userService.createFlow(this._userService.userID, data);
      this._router.navigate(['/' + this._userService.userID + '/content/' + response.id]);
      this.modalActiveClose.dismiss();
    } catch (err) {
      this._sharedService.showRequestErrors(err);
    }
  }

  /**
   * Clone flow item
   * @param item {object}
   * @returns {Promise<void>}
   */
  public async cloneFlowItem(item): Promise<any> {
    try {
      const response = await this._userService.copyFlow(this._userService.userID, item.id);
      this._flowFoldersService.flowItems.unshift(response);
      this._toastr.success('Nachricht kopiert nach "' + this._flowFoldersService.createFlowFolderName + '" mappe');
    } catch (err) {
      this._sharedService.showRequestErrors(err);
    }
  }

  /**
   * Access router
   */
  public accessRouter() {
    this.accessRouterCheck = true;
    setTimeout(() => {
      this.accessRouterCheck = false;
    }, 500);
  }

  /**
   * Open flow item to builder
   * @param item {object}
   */
  public openFlowItem(item) {
    if (this._sendPopup === true) {
      this.dataPreviewSidebar = item;
      this.rightSidebarPreview = true;
    } else {
      if (!this.accessRouterCheck) {
        this._router.navigate(['/' + this._userService.userID + '/content/' + item.id]);
      }
    }
  }

  /**
   * Create new folder
   * @param flowFolders {string}
   * @returns {Promise<void>}
   */
  public async addNewFolder(flowFolders): Promise<any> {
    this._flowFoldersService.folderPreload = true;
    const data = {
      name: flowFolders,
      parent: this._flowFoldersService.folderID
    };
    this.modalActiveClose.dismiss();
    try {
      const response = await this._userService.createFolders(this._userService.userID, data);
      this._flowFoldersService.flowTopFolders.push({
        id: response.new_folder.id,
        name: response.new_folder.name
      });
      this._flowFoldersService.folderPreload = false;
      this._flowFoldersService.flowFolders = response.folders;
      this._flowFoldersService.flowFolders.forEach((datas) => {
        this._flowFoldersService.traverse(datas, []);
      });
    } catch (err) {
      this._sharedService.showRequestErrors(err);
    }
  }

  /**
   * Open popup
   * @param content {any}
   * @param item {object}
   */
  public openVerticallyCenterFlow(content, item) {
    this.modalActiveClose = this._modalService.open(content);
    this.flowContant = item;
  }

  /**
   * Open popup
   * @param content {any}
   * @param item {object}
   */
  public openVerticallyCenterFolder(content, item) {
    this.modalActiveClose = this._modalService.open(content);
    this.folderContant = item;
  }

  /**
   * Open popup
   * @param content {any}
   * @param name {string}
   */
  public openVerticallyCentered(content, name) {
    this.modalActiveClose = this._modalService.open(content);
    this.createParentFolderName = name;
  }

  /**
   * Open popup
   * @param content {any}
   */
  public openVerticallyCenter(content) {
    this.modalActiveClose = this._modalService.open(content);
  }

  /**
   * Rename flow item
   * @param value {string}
   * @returns {Promise<void>}
   */
  public async renameFlowItem(value): Promise<void> {
    this.flowContant.name = value;
    const data = {
      name: value,
      status: true
    };
    try {
      await this._userService.updateFlow(this._userService.userID, this.flowContant['id'], data);
      this.modalActiveClose.dismiss();
    } catch (err) {
      this._sharedService.showRequestErrors(err);
    }
  }

  /**
   * Rename flow item
   * @param item {object}
   * @param array {Array}
   * @param index {number}
   * @returns {Promise<void>}
   */
  public async restoreFlow(item, array, index): Promise<void> {
    const data = {
      name: item.name,
      status: true
    };
    try {
      await this._userService.updateFlow(this._userService.userID, item.id, data);
      array.splice(index, 1);
      this._flowFoldersService.totalCount--;
    } catch (err) {
      this._sharedService.showRequestErrors(err);
    }
  }

  /**
   * Update folder name
   * @param value {string}
   * @param folder {object}
   * @returns {Promise<void>}
   */
  public async updateFolderName(value, folder): Promise<void> {
    this._flowFoldersService.folderPreload = true;
    folder.name = value;
    this.modalActiveClose.dismiss();

    const data = {
      name: value
    };
    try {
      const response = await this._userService.updateFolderName(this._userService.userID, folder.id, data);
      this._flowFoldersService.flowFolders = [];
      this._flowFoldersService.folderPreload = false;
      this._flowFoldersService.flowFolders = response.folders;
      this._flowFoldersService.flowFolders.forEach((datas) => {
        this._flowFoldersService.traverse(datas, []);
      });
    } catch (err) {
      this._sharedService.showRequestErrors(err);
    }
  }

  /**
   * Update folder name
   * @param value {string}
   * @param folder {object}
   * @returns {Promise<void>}
   */
  public async updateFolderNames(value, folder): Promise<void> {
    this._flowFoldersService.folderPreload = true;

    const data = {
      name: value
    };
    try {
      const response = await this._userService.updateFolderName(this._userService.userID, folder.id, data);
      this._flowFoldersService.flowFolders = [];
      this._flowFoldersService.folderPreload = false;
      this._flowFoldersService.flowFolders = response.folders;
      this._flowFoldersService.flowFolders.forEach((datas) => {
        this._flowFoldersService.traverse(datas, []);
      });
    } catch (err) {
      this._sharedService.showRequestErrors(err);
    }
  }

  /**
   * Delete flow
   * @param item {number}
   * @param draftArray {array}
   * @param index {number}
   * @returns {Promise<void>}
   */
  public async deleteFlow(item, draftArray, index): Promise<any> {
    this.accessRouterCheck = true;
    try {
      const response = await this._userService.deleteFlow(this._userService.userID, item.id);
      draftArray.splice(index, 1);
      this.accessRouterCheck = false;
      this._flowFoldersService.totalCount--;
    } catch (err) {
      this._sharedService.showRequestErrors(err);
    }
  }

  /**
   * Delete folder
   * @param item {number}
   * @param draftArray {array}
   * @param index {number}
   * @returns {Promise<void>}
   */
  public async deleteFolder(item, draftArray, index): Promise<any> {
    try {
      await this._userService.deleteFolder(this._userService.userID, item.id);
      draftArray.splice(index, 1);
      this._flowFoldersService.folderPreload = true;
      this._flowFoldersService.flowFolders = [];
      this.getFolders();
    } catch (err) {
      this._sharedService.showRequestErrors(err);
    }
  }




  // public selectFlowToContent(data) {
  //   this._sharedService.widgetEditSidebarContent.widget_content.push(data);
  //   this.closePopup();
  // }

}
