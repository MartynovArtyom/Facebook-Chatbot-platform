import { Component, OnInit } from '@angular/core';
import { SequenceItem } from '../../../../../../../entities/models-user';
import { UserService } from '../../../../../../services/user.service';
import { SharedService } from '../../../../../../services/shared.service';
import { CompanyManagerService } from '../../../../../../services/company-manager.service';
import { NgbModal } from '@ng-bootstrap/ng-bootstrap';
import { ToastrService } from 'ngx-toastr';
import { environment } from '../../../../../../../environments/environment';

@Component({
  selector: 'app-autoresponder',
  templateUrl: './autoresponder.component.html',
  styleUrls: ['./autoresponder.component.scss']
})
export class AutoresponderComponent implements OnInit {

  public autoresponderItems = Array<SequenceItem>();
  public preloader = true;
  public preloaderPopup = true;
  public copied = false;
  public sequnceUpdate = '';
  public shareData: any;
  public modalActiveClose: any;

  public removedId: any;
  public removedI: any;

  constructor(
    public _userService: UserService,
    public companyManagerService: CompanyManagerService,
    private readonly _modalService: NgbModal,
    private readonly _toastr: ToastrService,
    private _sharedService: SharedService
  ) { }

  ngOnInit() {
    this.getSequences();
  }

  /**
   * Get sequences
   * @returns {Promise<void>}
   */
  public async getSequences(): Promise<any> {
    try {
      const data = await this._userService.getSequences(this._userService.userID, '', 1);
      data.items.forEach((response) => {
        this.autoresponderItems.push(response);
      });
      this.preloader = false;
      this._sharedService.preloaderView = false;
    } catch (err) {
      this._sharedService.showRequestErrors(err);
    }
  }

  /**
   * Copy sequence
   * @param id {number}
   * @returns {Promise<void>}
   */
  public async copySequence(id): Promise<any> {
    try {
      const res = await this._userService.copySequence(this._userService.userID, id);
      this.autoresponderItems.unshift(res);
      this.companyManagerService.sequenceData.unshift(res);
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
      tab.name = title;
      const res = this.autoresponderItems.find(item => item.id === sequnceUpdate.id);
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
      const iten = this.autoresponderItems.splice(this.removedI, 1);
      this.companyManagerService.sequenceData.forEach((data, i) => {
        if (iten[0].id === data.id) {
          this.companyManagerService.sequenceData.splice(i, 1);
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
   * Open popup
   * @param content
   */
  public openVerticallyCenter(content, sequence) {
    this.sequnceUpdate = sequence;
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
    this.getShareSequence(item.id);

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
   * Get share Sequence
   * @param id {number}
   * @returns {Promise<any>}
   */
  public async getShareSequence(id): Promise<any> {
    try {
      const response = await this._userService.getShareSequence(this._userService.userID, id);
      this.shareData = response;
      this.shareData['url'] = environment.originUrl + '/share-autoresponder/' + this.shareData.token;
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
      await this._userService.updateShareSequenceStatus(this._userService.userID, this.shareData.id, data);
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
