import { Component, Input, OnInit } from '@angular/core';
import { UUID } from 'angular2-uuid';
import { NgbModal } from '@ng-bootstrap/ng-bootstrap';
import { ActivatedRoute, Router } from '@angular/router';
import { ToastrService } from 'ngx-toastr';
import { UserService } from '../../../../../../services/user.service';
import { DragulaService } from 'ng2-dragula';
import { SharedService } from '../../../../../../services/shared.service';
import {CompanyManagerService} from '../../../../../../services/company-manager.service';

@Component({
  selector: 'app-sequences',
  templateUrl: './sequences.component.html',
  styleUrls: ['./sequences.component.scss']
})
export class SequencesComponent implements OnInit {
  public _viewId: any;
  @Input('viewId') set viewId(viewId) {
    if (viewId) {
      this._viewId = viewId;
    }
  }
  get viewId() {
    return this._viewId;
  }

  public preloader = true;
  public currentPostId: any;
  public pageId: string;
  public sequenceName = '';
  public sequence: any;
  public modalActiveClose: any;
  public keywordSelected: any;
  public sequenceItem: any;
  public uuidDrag = UUID.UUID();

  constructor(
    public readonly _userService: UserService,
    public readonly _route: ActivatedRoute,
    public readonly _sharedService: SharedService,
    public readonly _modalService: NgbModal,
    public readonly _router: Router,
    public readonly companyManagerService: CompanyManagerService,
    private _dragulaService: DragulaService,
    private readonly _toastr: ToastrService
  ) {

    this._dragulaService.createGroup('SEQUENCE' + this.uuidDrag, {
      moves: (el, container, handle) => {
        return handle.className === 'fas fa-grip-vertical';
      }
    });

    this._dragulaService.dropModel('SEQUENCE' + this.uuidDrag).subscribe((value) => {
      this.updateSequencePosition();
    });

  }

  ngOnInit() {
    this.pageId = localStorage.getItem('page');
    this.currentPostId = this._viewId;
    this.getSequenceById();
  }

  /**
   * Check flow item
   * @param item {object}
   */
  public checkFlowItem(item) {
    if (!item.flow) {
      item['status'] = false;
      this._toastr.error('Bitte erstellen Sie Sequenznachrichteninhalte oder hängen Sie eine vorhandene an');
    }
  }

  /**
   * Update sequence item
   * @param item
   * @param key
   * @param id
   * @returns {Promise<void>}
   */
  public async updateSequenceItem(item, key, id): Promise<any> {
    item['openDelay'] = false;
    const data = {};
    if (key === 'status') {
      item['status'] = !item['status'];
      data[key] = item['status'];
    } else if (key === 'flowID') {
      data[key] = id;
    } else {
      data[key] = id;
    }

    try {
      await this._userService.updateSequenceItem(this._userService.userID, data, this.sequence.id, item.id);
      if (key === 'status') {
        this._toastr.success('Sequenzmeldung gespeichert!');
      } else if (key === 'delay') {
        item.delay.value = id.value;
        item.delay.type = id.type;
        this._toastr.success('Einstellungen aktualisiert!');
      }
    } catch (err) {
      if (err.error.error.message === 'Please create or select Flow!') {
        item.status = false;
      }
      this._sharedService.showRequestErrors(err);
    }
  }

  /**
   * Update sequence
   * @param sequnceUpdate {object}
   * @param title {string}
   * @returns {Promise<void>}
   */
  public async updateSequenceTitle(sequnceUpdate, title): Promise<any> {
    const data = {
      title: title
    };
    try {
      await this._userService.updateSequence(this._userService.userID, data, sequnceUpdate.id);
      this._toastr.success('Sequenz gespeichert!');
    } catch (err) {
      this._sharedService.showRequestErrors(err);
    }
  }

  /**
   * Remove sequence item
   * @param item {object}
   * @param i {number}
   * @returns {Promise<void>}
   */
  public async removeSequenceItem(item, i): Promise<any> {
    try {
      await this._userService.removeSequenceItem(this._userService.userID, this.sequence.id, item.id);
      this.sequence.items.splice(i, 1);
    } catch (err) {
      this._sharedService.showRequestErrors(err);
    }
  }

  /**
   * Update sequence items number position
   * @returns {Promise<void>}
   */
  public async updateSequencePosition() {
    const data = [];
    this.sequence.items.forEach((item, i) => {
      data.push({
        id: item.id,
        number: i + 1
      });
    });
    try {
      await this._userService.updateSequencePosition(this._userService.userID, data, this.sequence.id);
    } catch (err) {
      this._sharedService.showRequestErrors(err);
    }
  }

  /**
   * Open popup
   * @param content {any}
   * @param keyword {object}
   */
  public openVerticallyCentered(content, keyword) {
    this._sharedService.sequenceItemId = keyword.id;
    this.keywordSelected = keyword;
    this.modalActiveClose = this._modalService.open(content, {
      'size': 'lg',
      windowClass: 'flow-popup',
      backdropClass: 'light-blue-backdrop'});
  }

  /**
   * Create new flow to keyboard
   * @param keyword {object}
   */
  public async createFlow(keyword): Promise<any> {
    this._sharedService.sequenceItemId = keyword.id;
    const data = {
      name: 'Sequenzmeldung ' + keyword.id,
      type: 5,
      folderID: null
    };

    try {
      const response = await this._userService.createFlow(this._userService.userID, data);
      this.updateSequenceItem(keyword, 'flowID', response.id).then(() => {
        const dataResponse = {
          flow: {
            id: response.id,
            name: response.name
          }
        };
        this.routerToEditSequenceItem(dataResponse);
      });
    } catch (err) {
      this._sharedService.showRequestErrors(err);
    }
  }

  /**
   * Close delay popover container
   * @param item {object}
   * @param status {boolean}
   */
  public closeDelayContainer(item, status) {
    if (this._userService.userPageInfo.role !== 4) {
      item['openDelay'] = status;
      if (status === true) {
        this.sequenceItem = JSON.parse(JSON.stringify(item));
      }
    }
  }

  /**
   * Route to edit keyword flow
   * @param response {object}
   */
  public routerToEditSequenceItem(response) {
    let count = 0;
    this.companyManagerService.dataSecondLevelTabs.forEach(data => {
      data.status = false;
      if (data.id === response.flow.id) {
        count++;
        data.status = true;
        this.companyManagerService.activeCampaignItem = data;
      }
    });

    if (count === 0) {
      this.companyManagerService.dataSecondLevelTabs.push({
        name: response.flow.name,
        type: 'flow',
        status: true,
        id: response.flow.id
      });

      this.companyManagerService.activeCampaignItem = {
        name: response.flow.name,
        type: 'flow',
        status: true,
        id: response.flow.id
      };
    }

    this._sharedService.sequenceItemId = response.id;
    this._router.navigate(['/' + this._userService.userID + '/company-manager'],
      {queryParams: {view: 'flow', id: response.flow.id}});
  }

  /**
   * Get autoposting by id
   * @returns {Promise<void>}
   */
  public async getSequenceById(): Promise<any> {
    try {
      this.sequence = await this._userService.getSequenceById(this.pageId , this.currentPostId);
      this._sharedService.sequenceId = this.sequence.id;
      this.sequenceName = this.sequence.title;
      this.preloader = false;
      this._sharedService.preloaderView = false;
    } catch (err) {
      this._sharedService.showRequestErrors(err);
    }
  }

  /**
   * Create new sequence message
   * @returns {Promise<void>}
   */
  public async createNewSequenceMessage(): Promise<any> {
    const data = {
      number: this.sequence.items.length + 1
    };

    try {
      const res = await this._userService.createNewSequenceMessage(this._userService.userID, data, this.sequence.id);
      this._toastr.success('Nachricht erstellt!');
      this.sequence.items.push(res);
    } catch (err) {
      this._sharedService.showRequestErrors(err);
    }
  }

}
