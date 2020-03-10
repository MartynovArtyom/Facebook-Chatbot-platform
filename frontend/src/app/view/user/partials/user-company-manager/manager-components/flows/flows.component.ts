import { Component, OnInit } from '@angular/core';
import { UserService } from '../../../../../../services/user.service';
import { FlowItems } from '../../../../../../../entities/models-user';
import { SharedService } from '../../../../../../services/shared.service';
import { Router } from '@angular/router';
import { ToastrService } from 'ngx-toastr';
import { NgbModal } from '@ng-bootstrap/ng-bootstrap';
import { CompanyManagerService } from '../../../../../../services/company-manager.service';

@Component({
  selector: 'app-flows',
  templateUrl: './flows.component.html',
  styleUrls: ['./flows.component.scss']
})
export class FlowsComponent implements OnInit {
  public preloader = true;
  public loadPagination = true;
  public pagination = 1;
  public totalCount = 0;
  public flowItems = Array<FlowItems>();
  public modifiedCheck = 'DESC';
  public modalActiveClose: any;
  public flowContant: any;
  public statusFlow = true;

  constructor(
    private _userService: UserService,
    private _sharedService: SharedService,
    private _toastr: ToastrService,
    private _modalService: NgbModal,
    private _router: Router,
    public companyManagerService: CompanyManagerService
  ) { }

  ngOnInit() {
    this.flowItems = [];
    this.getAllFlows();
  }

  /**
   * Open flows list by status
   * @param status {boolean}
   */
  public openFlowListByStatus(status) {
    if (this.statusFlow !== status) {
      this.preloader = false;
      this.statusFlow = status;
      this.flowItems = [];
      this.pagination = 1;
      this.getAllFlows();
    }
  }

  /**
   * Get flows items
   * @returns {Promise<any>}
   */
  public async getAllFlows(): Promise<any> {
    const data = {
      page: this.pagination,
      folderID: '',
      modified: this.modifiedCheck,
      status: this.statusFlow
    };

    try {
      const response = await this._userService.getFlowsById(this._userService.userID, data);
      response.items.forEach((item) => {
        this.flowItems.push(item);
      });
      this.totalCount = response.pagination.total_count;
      this._sharedService.preloaderView = false;
      this.preloader = false;
      this.loadPagination = false;
    } catch (err) {
      this._sharedService.showRequestErrors(err);
    }
  }

  /**
   * Open flow item to builder
   * @param item {object}
   */
  public openFlowItem(item) {
    this._router.navigate(['/' + this._userService.userID + '/content/' + item.id]);
  }

  /**
   * Modified flows
   * @param value {string}
   */
  public changeValueModified(value) {
    this.modifiedCheck = value;
    this.flowItems = [];
    this.pagination = 0;
    this.loadMore();
  }

  /**
   * Delete flow
   * @param item {number}
   * @param draftArray {array}
   * @param index {number}
   * @returns {Promise<void>}
   */
  public async deleteFlow(item, draftArray, index): Promise<any> {
    try {
      const response = await this._userService.deleteFlow(this._userService.userID, item.id);
      draftArray.splice(index, 1);
      this.totalCount--;

      this.companyManagerService.dataFirstLevelTabs.forEach((tab, indexFirstTab) => {
        tab.children.forEach((child, indexSecondTab) => {
          if (child.id === item.id) {
            if (tab.type === 'widget') {
              if (child.name === 'Bearbeiten') {
                this.companyManagerService.dataFirstLevelTabs.splice(indexFirstTab, 1);
                const deleteIndex = this.companyManagerService.widgetData.findIndex(widget => widget.id === tab.id);
                if (deleteIndex !== -1) {
                  this.companyManagerService.widgetData.splice(deleteIndex, 1);
                }
              } else {
                this.deleteWidgetItem(item);
                tab.children.splice(indexSecondTab, 1);
                tab.children[indexSecondTab - 1].status = true;
              }
            } else if (tab.type === 'keywords') {
              if (child.name === 'Bearbeiten') {
                this.deleteWidgetItem(item);
                this.companyManagerService.dataFirstLevelTabs.splice(indexFirstTab, 1);
              } else {
                this.deleteWidgetItem(item);
                tab.children.splice(indexSecondTab, 1);
                tab.children[indexSecondTab - 1].status = true;
              }
            } else if (tab.type === 'autoresponder') {
              this.deleteWidgetItem(item);
              tab.children.splice(indexSecondTab, 1);
              tab.children[indexSecondTab - 1].status = true;
            }
          }

        });
      });
    } catch (err) {
      this._sharedService.showRequestErrors(err);
    }
  }

  /**
   * Delete widget item
   * @param item {object}
   */
  public deleteWidgetItem(item) {
    const deleteIndex = this.companyManagerService.widgetData.findIndex(widget =>
      widget.flow.id === item.id);
    if (deleteIndex !== -1) {
      this.companyManagerService.widgetData.splice(deleteIndex, 1);
    }
  }

  /**
   * Delete flow
   * @param item {number}
   * @param draftArray {array}
   * @param index {number}
   * @returns {Promise<void>}
   */
  public async deleteFlowToTrash(item, draftArray, index): Promise<any> {
    const data = {
      status: false
    };
    try {
      await this._userService.updateFlow(this._userService.userID, item.id, data);
      draftArray.splice(index, 1);
      this.totalCount--;
      item.status = false;
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
      this.flowItems.unshift(response);
      this._toastr.success('Nachricht erstellt!');
    } catch (err) {
      this._sharedService.showRequestErrors(err);
    }
  }

  /**
   * Load more
   */
  public loadMore() {
    this.pagination++;
    this.loadPagination = true;
    this.getAllFlows();
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
      item.status = true;
      this.totalCount--;
    } catch (err) {
      this._sharedService.showRequestErrors(err);
    }
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
}
