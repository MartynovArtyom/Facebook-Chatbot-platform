import { Component, OnInit } from '@angular/core';
import { UserService } from '../../../../../../services/user.service';
import { SharedService } from '../../../../../../services/shared.service';
import { CompanyManagerService } from '../../../../../../services/company-manager.service';
import { ToastrService } from 'ngx-toastr';
import { NgbModal } from '@ng-bootstrap/ng-bootstrap';
import { environment } from '../../../../../../../environments/environment';

@Component({
  selector: 'app-company',
  templateUrl: './company.component.html',
  styleUrls: ['./company.component.scss']
})
export class CompanyComponent implements OnInit {
  public companyItems = [];
  public preloader = true;
  public preloaderPopup = true;
  public copied = false;
  public shareData: any;
  public modalActiveClose: any;
  public companyUpdate = '';

  public removedId: any;
  public removedI: any;

  constructor(
    public _userService: UserService,
    private _toastr: ToastrService,
    public companyManagerService: CompanyManagerService,
    public _modalService: NgbModal,
    private _sharedService: SharedService
  ) { }

  ngOnInit() {
    this.getAllWidgets();
  }

  /**
   * Router to builder
   * @param item {object}
   */
  public routeToBuilder(item) {
    (item.type === 12) ? this.companyManagerService.navigateToTab(item, 'newAutoresponder', true) :
      this.companyManagerService.navigateToTab(item, 'widget', true);
  }

  /**
   * Get widgets
   * @returns {Promise<void>}
   */
  public async getAllWidgets(): Promise<any> {
    try {
      const data = await this._userService.getAllWidgets(this._userService.userID);
      data.items.forEach((response) => {
        this.companyItems.push(response);
      });
      this.preloader = false;
      this._sharedService.preloaderView = false;
    } catch (err) {
      this._sharedService.showRequestErrors(err);
    }
  }

  /**
   * Choice growth tool
   * @param item {object}
   * @param value {string}
   * @returns {Promise<void>}
   */
  public async changedGrowthTool(item, value): Promise<any> {
    const data = {
      status: value
    };
    try {
      await this._userService.updateWidget(this._userService.userID, item.id, data);
      item.status = value;
      (value) ? this._toastr.success('Widget aktiviert!') : this._toastr.show('Widget deaktiviert');
    } catch (err) {
      this._sharedService.showRequestErrors(err);
    }
  }

  /**
   * Rename company
   * @param item {object}
   * @param value {string}
   * @returns {Promise<any>}
   */
  public async renameCompany(item, value): Promise<any> {
    const data = {
      name: value
    };

    try {
      await this._userService.updateWidget(this._userService.userID, item.id, data);
      const widget = this.companyManagerService.widgetData.filter(res => res.id === item.id);
      const tab = this.companyManagerService.dataFirstLevelTabs.filter(res => res.id === item.id);
      if (tab && tab[0]) {
        tab[0].name = value;
      }
      widget[0].name = value;
      item.name = value;
      this.modalActiveClose.dismiss();
    } catch (err) {
      this._sharedService.showRequestErrors(err);
    }
  }

  /**
   * Open popup
   * @param content {any}
   * @param item {object}
   */
  public openVerticallyCenter(content, item) {
    this.companyUpdate = item;
    this.modalActiveClose = this._modalService.open(content, { backdropClass: 'create-sequence' });
  }

  /**
   * Open popup
   * @param content {any}
   * @param item {object}
   */
  public openVerticallyCentered(content, item) {
    this.preloaderPopup = true;
    this.copied = false;
    this.shareData = null;
    this.getShareCompany(item.id);

    this.modalActiveClose = this._modalService.open(content, { backdropClass: 'create-sequence' });
  }

  /**
   * Open PopUp
   * @param content
   * @param id
   * @param i
   */
  public openVerticallyCenterRemove(content, id, i) {
    this.removedId = id;
    this.removedI = i;

    this.modalActiveClose = this._modalService.open(content, { backdropClass: 'create-sequence' });
  }

  /**
   * Get share company
   * @param id {number}
   * @returns {Promise<any>}
   */
  public async getShareCompany(id): Promise<any> {
    try {
      const response = await this._userService.getShareCompany(this._userService.userID, id, 'widget');
      this.shareData = response;
      this.shareData['url'] = environment.originUrl + '/share-kampagnen/' + this.shareData.token;
      this.preloaderPopup = false;
    } catch (err) {
      this._sharedService.showRequestErrors(err);
    }
  }

  /**
   * Edit share status
   * @returns {Promise<any>}
   */
  public async editShareStatus(): Promise<any> {
    this.shareData.status = !this.shareData.status;

    const data = {
      status: this.shareData.status
    };
    try {
      await this._userService.updateShareStatus(this._userService.userID, this.shareData.id, data);
    } catch (err) {
      this._sharedService.showRequestErrors(err);
    }
  }

  /**
   * Remove Widget
   */
  public async deleteWidget(): Promise<void> {
    try {
      await this._userService.deleteWidgets(this._userService.userID, this.removedId);
      const iten = this.companyItems.splice(this.removedI, 1);
      this.companyManagerService.widgetData.forEach((data, index) => {
        if (iten[0].id === data.id) {
          this.companyManagerService.widgetData.splice(index, 1);
        }
      });
      const tabIndex = this.companyManagerService.dataFirstLevelTabs.findIndex(res => res.id === this.removedId);
      if (tabIndex !== -1) {
        this.companyManagerService.dataFirstLevelTabs.splice(tabIndex, 1);
      }
    } catch (err) {
      this._sharedService.showRequestErrors(err);
    }
  }

  /**
   * Duplicate widget
   * @param id {number}
   * @returns {Promise<any>}
   */
  public async duplicateWidget(id): Promise<any> {
    try {
      const response = await this._userService.copyWidget(this._userService.userID, id);
      this.companyItems.unshift(response);
      this.companyManagerService.widgetData.unshift(response);
    } catch (err) {
      this._sharedService.showRequestErrors(err);
    }
  }

  /**
   * Copy to clipboard link for users
   * @param item {string}
   */
  public copyToClipboard(item) {
    document.addEventListener('copy', (e: ClipboardEvent) => {
      e.clipboardData.setData('text/plain', (item));
      e.preventDefault();
      document.removeEventListener('copy', null);
    });
    document.execCommand('copy');
    this.copied = true;
  }

}
