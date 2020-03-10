import { Component, OnInit } from '@angular/core';
import { CompanyManagerService } from '../../../../../../services/company-manager.service';
import { UserService } from '../../../../../../services/user.service';
import { SharedService } from '../../../../../../services/shared.service';
import { ActivatedRoute } from '@angular/router';
import { NgbModal } from '@ng-bootstrap/ng-bootstrap';
import { ToastrService } from 'ngx-toastr';
import {environment} from '../../../../../../../environments/environment';

@Component({
  selector: 'app-search-company',
  templateUrl: './search-company.component.html',
  styleUrls: ['./search-company.component.scss']
})
export class SearchCompanyComponent implements OnInit {
  public pagination = 1;
  public total = 1;
  public dataCompany = [];
  public preloader = true;
  public preloaderPagination = true;
  public modalActiveClose: any;
  public sequnceUpdate: any;
  public shareData: any;
  public preloaderPopup = true;
  public copied = false;

  public removedId: any;
  public removedI: any;

  constructor(
    private readonly _sharedService: SharedService,
    private readonly _route: ActivatedRoute,
    public readonly companyManagerService: CompanyManagerService,
    public readonly _userService: UserService,
    public readonly _toastr: ToastrService,
    public readonly _modalService: NgbModal
  ) {
  }

  ngOnInit() {
    setTimeout(() => {
      this._route.queryParams.subscribe(data => {
        if (data.text) {
          this.preloader = true;
          this.dataCompany = [];
          this.pagination = 1;
          this.companyManagerService.searchKampaign = data.text;
          this.getSearchCompany();
        }
      });
    }, 500);
  }

  /**
   * Load pagination
   */
  public loadPagination() {
    this.preloaderPagination = true;
    this.pagination++;
    this.getSearchCompany();
  }

  /**
   * Get searching companies
   * @returns {Promise<any>}
   */
  public async getSearchCompany(): Promise<any> {
    try {
      const response = await this._userService.getSearchCampaign(this._userService.userID,
                                                                 this.pagination,
                                                                 this.companyManagerService.searchKampaign);
      response.items.forEach(item => {
        this.dataCompany.push(item);
      });
      this.total = response.pagination.total_count;

      this.preloader = false;
      this.preloaderPagination = false;
    } catch (err) {
      this._sharedService.showRequestErrors(err);
    }
  }

  /**
   * Open popup
   * @param content
   * @param sequence
   */
  public openVerticallyCenter(content, sequence) {
    this.sequnceUpdate = sequence;
    this.modalActiveClose = this._modalService.open(content, { backdropClass: 'create-sequence' });
  }

  /**
   * Open popup
   * @param content {any}
   * @param item {object}
   * @param type {string}
   */
  public openVerticallyCentered(content, item, type) {
    this.preloaderPopup = true;
    this.copied = false;
    this.shareData = null;
    if (type === 'widget') {
      this.getShareCompany(item.id, type);
    } else {
      this.getShareSequence(item.id);
    }

    this.modalActiveClose = this._modalService.open(content, { backdropClass: 'create-sequence' });
  }

  /**
   * Open Popup
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
   * Copy sequence
   * @param id {number}
   * @returns {Promise<void>}
   */
  public async copySequence(id): Promise<any> {
    try {
      const res = await this._userService.copySequence(this._userService.userID, id);
      res['campaign_type'] = 'sequence';
      this.dataCompany.unshift(res);
      this.companyManagerService.sequenceData.unshift(res);
      this.total++;
    } catch (err) {
      this._sharedService.showRequestErrors(err);
    }
  }

  /**
   * Update sequence
   * @param sequnceUpdate {object}
   * @param title {string}
   * @returns {Promise<void>}
   */
  public async updateSequence(sequnceUpdate, title): Promise<any> {
    const data = {
      title: title
    };
    try {
      await this._userService.updateSequence(this._userService.userID, data, sequnceUpdate.id);
      const req = this.companyManagerService.sequenceData.find(item => item.id === sequnceUpdate.id);
      const tab = this.companyManagerService.dataFirstLevelTabs.find(item => item.id === sequnceUpdate.id);
      req.title = title;
      if (tab) {
        tab.name = title;
      }
      const res = this.dataCompany.find(item => item.id === sequnceUpdate.id);
      res.title = title;
      this.modalActiveClose.dismiss();
      this._toastr.success('Autoresponder gespeichert!');
    } catch (err) {
      this._sharedService.showRequestErrors(err);
    }
  }

  /**
   * Delete Sequence
   */
  public async deleteSequence(): Promise<any> {
    try {
      await this._userService.deleteSequence(this._userService.userID, this.removedId);
      const iten = this.dataCompany.splice(this.removedI, 1);
      this.companyManagerService.sequenceData.forEach((data, i) => {
        if (iten[0].id === data.id) {
          this.companyManagerService.sequenceData.splice(i, 1);
        }
      });
      const tabIndex = this.companyManagerService.dataFirstLevelTabs.findIndex(res => res.id === this.removedId);
      if (tabIndex !== -1) {
        this.companyManagerService.dataFirstLevelTabs.splice(tabIndex, 1);
      }
      this.total--;
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
      (value) ? this._toastr.success('Kampagne aktiviert!') : this._toastr.show('Kampagne deaktiviert');
    } catch (err) {
      this._sharedService.showRequestErrors(err);
    }
  }

  /**
   * Delete Widget
   */
  public async deleteWidget(): Promise<void> {
    try {
      await this._userService.deleteWidgets(this._userService.userID, this.removedId);
      const iten = this.dataCompany.splice(this.removedI, 1);
      this.companyManagerService.widgetData.forEach((data, index) => {
        if (iten[0].id === data.id) {
          this.companyManagerService.widgetData.splice(index, 1);
        }
      });
      const tabIndex = this.companyManagerService.dataFirstLevelTabs.findIndex(res => res.id === this.removedId);
      if (tabIndex !== -1) {
        this.companyManagerService.dataFirstLevelTabs.splice(tabIndex, 1);
      }
      this.total--;
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
      response['campaign_type'] = 'widget';
      this.dataCompany.unshift(response);
      this.companyManagerService.widgetData.unshift(response);
      this.total++;
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
      widget[0].name = value;
      if (tab && tab[0]) {
        tab[0].name = value;
      }
      item.name = value;
      this.modalActiveClose.dismiss();
    } catch (err) {
      this._sharedService.showRequestErrors(err);
    }
  }

  /**
   * Get share company
   * @param id {number}
   * @param type {string}
   * @returns {Promise<any>}
   */
  public async getShareCompany(id, type): Promise<any> {
    try {
      this.shareData = await this._userService.getShareCompany(this._userService.userID, id, type);
      this.shareData['url'] = environment.originUrl + '/share-kampagnen/' + this.shareData.token;
      this.shareData['type'] = 'widget';
      this.preloaderPopup = false;
    } catch (err) {
      this._sharedService.showRequestErrors(err);
    }
  }

  /**
   * Get share Sequence
   * @param id {number}
   * @returns {Promise<any>}
   */
  public async getShareSequence(id): Promise<any> {
    try {
      this.shareData = await this._userService.getShareSequence(this._userService.userID, id);
      this.shareData['url'] = environment.originUrl + '/share-autoresponder/' + this.shareData.token;
      this.shareData['type'] = 'sequence';
      this.preloaderPopup = false;
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
      if (this.shareData.type === 'sequence') {
        await this._userService.updateShareSequenceStatus(this._userService.userID, this.shareData.id, data);
      } else {
        await this._userService.updateShareStatus(this._userService.userID, this.shareData.id, data);
      }
    } catch (err) {
      this._sharedService.showRequestErrors(err);
    }
  }

}
