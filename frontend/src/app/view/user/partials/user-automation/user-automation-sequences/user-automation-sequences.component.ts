import { Component, OnInit } from '@angular/core';
import { SequenceItem } from '../../../../../../entities/models-user';
import { NgbModal } from '@ng-bootstrap/ng-bootstrap';
import { UserService } from '../../../../../services/user.service';
import { Router } from '@angular/router';
import { SharedService } from '../../../../../services/shared.service';
import { ToastrService } from 'ngx-toastr';

@Component({
  selector: 'app-user-automation-sequences',
  templateUrl: './user-automation-sequences.component.html',
  styleUrls: ['./user-automation-sequences.component.scss']
})
export class UserAutomationSequencesComponent implements OnInit {
  public preloader = true;
  public modalActiveClose: any;
  public pageId: string;
  public currentPage = 1;
  public disabledRoutes = false;
  public sequnceUpdate = '';
  public sequencesItems = Array<SequenceItem>();

  constructor(
    private readonly _modalService: NgbModal,
    public readonly _userService: UserService,
    public readonly _router: Router,
    public readonly _toastr: ToastrService,
    private readonly _sharedService: SharedService
  ) {
    this.pageId = localStorage.getItem('page');
  }

  ngOnInit() {
    this.getSequences('');
  }

  /**
   * Disable router
   */
  public disabledRoute() {
    this.disabledRoutes = true;
    setTimeout(() => {
      this.disabledRoutes = false;
    }, 50);
  }

  /**
   * Get sequences
   * @returns {Promise<void>}
   */
  public async getSequences(search): Promise<any> {
    try {
      const data = await this._userService.getSequences(this.pageId, search, this.currentPage);
      data.items.forEach((response) => {
        this.sequencesItems.push(response);
      });
      this.preloader = false;
      this._sharedService.preloaderView = false;
    } catch (err) {
      this._sharedService.showRequestErrors(err);
    }
  }

  /**
   * Create sequence
   * @returns {Promise<void>}
   */
  public async createSequence(value): Promise<any> {
    const data = {
      title: value
    };

    try {
      const response = await this._userService.createSequence(this.pageId, data);
      this.modalActiveClose.dismiss();
      this._router.navigate(['/' + this.pageId + '/automation/sequences/' + response.id]);
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
      this.sequencesItems.unshift(res);
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
      const res = this.sequencesItems.find(item => item.id === sequnceUpdate.id);
      res.title = title;
      this.modalActiveClose.dismiss();
      this._toastr.success('Sequenz gespeichert!');
    } catch (err) {
      this._sharedService.showRequestErrors(err);
    }
  }

  /**
   * Delete sequence
   * @param item {object}
   * @param index {number}
   * @returns {Promise<void>}
   */
  public async deleteSequence(item, index): Promise<any> {
    try {
      await this._userService.deleteSequence(this._userService.userID, item.id);
      this.sequencesItems.splice(index, 1);
    } catch (err) {
      this._sharedService.showRequestErrors(err);
    }
  }

  /**
   * Navigate to sequence by ID
   * @param id {number}
   */
  public navigateToSequence(id) {
    if (!this.disabledRoutes) {
      this._router.navigate(['/' + this.pageId + '/automation/sequences/' + id]);
    }
  }

  /**
   * Open popup
   * @param content
   */
  public openVerticallyCentered(content) {
    this.modalActiveClose = this._modalService.open(content, { backdropClass: 'create-sequence' });
  }

  /**
   * Open popup
   * @param content
   */
  public openVerticallyCenter(content, sequence) {
    this.sequnceUpdate = sequence;
    this.modalActiveClose = this._modalService.open(content, { backdropClass: 'create-sequence' });
  }

}
