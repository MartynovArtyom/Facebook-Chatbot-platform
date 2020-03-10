import { Component, OnInit, ViewChild } from '@angular/core';
import { UserService } from '../../../../../services/user.service';
import { SharedService } from '../../../../../services/shared.service';
import { ActivatedRoute } from '@angular/router';
import { Arrow, ItemClass, RequestData } from '../../../../../modules/messages-builder/builder/builder-interface';
import { UUID } from 'angular2-uuid';
import { BuilderService } from '../../../../../modules/messages-builder/services/builder.service';
import { ArrowsService } from '../../../../../modules/messages-builder/services/arrows.service';
import { AudienceService } from '../../../../../services/audience.service';
import { ToastrService } from 'ngx-toastr';
import { NgbModal } from '@ng-bootstrap/ng-bootstrap';

@Component({
  selector: 'app-user-broadcasts-draft-edit',
  templateUrl: './user-broadcasts-draft-edit.component.html',
  styleUrls: ['./user-broadcasts-draft-edit.component.scss']
})
export class UserBroadcastsDraftEditComponent implements OnInit {
  @ViewChild('dateTime') public dateTime: any;
  @ViewChild('content') public content: any;

  public preloader = true;
  public pageId: string;
  public currentPostId: any;
  public modalActiveClose: any;
  public broadcast: any;
  public broadcastType = 1;
  public broadcastSendType = 1;
  public broadcastPushType = 1;
  public broadcastTag = null;
  public openTab = 'setting';
  public publish = false;
  public broadcastTypeCheck = false;

  constructor(
    public readonly _userService: UserService,
    public readonly _sharedService: SharedService,
    private readonly _builderService: BuilderService,
    private readonly _arrowsService: ArrowsService,
    private readonly _audienceService: AudienceService,
    private readonly _toastr: ToastrService,
    public readonly _modalService: NgbModal,
    private readonly _route: ActivatedRoute
  ) {
    this.pageId = localStorage.getItem('page');
    this.currentPostId = this._route.snapshot.params['id'];
  }

  ngOnInit() {
    this._sharedService.conditionArray = [];
    this._audienceService.getSubscribersFilter().then(() => {
      this.getBroadcastsById(this.currentPostId);
    });
  }

  /**
   * Get broadcasts by ID
   * @returns {Promise<void>}
   */
  public async getBroadcastsById(id): Promise<any> {
    try {
      const data = await this._userService.getBroadcastsById(this.pageId, id);
      this.broadcastType = data.type;
      this.broadcastTag = data.tag;
      this.broadcastPushType = data.pushType;
      this._sharedService.conditionArray = data.targeting;
      this._sharedService.flowId = data.flow.id;
      this._sharedService.nameFlow = data.flow.name;
      this._arrowsService.linksArray = [];
      this._builderService.requestDataAll = new RequestData(data.flow);

      if (this._builderService.requestDataAll.draftItems.length > 0) {
        this._builderService.requestDataItems = this._builderService.requestDataAll.draftItems;
      } else if (this._builderService.requestDataAll.items.length > 0) {
        this._builderService.requestDataItems = this._builderService.requestDataAll.items;
      } else {
        const ids = UUID.UUID();
        this._builderService.requestDataAll.draftItems.push(new ItemClass({
          uuid: ids,
          name: 'Sende Nachricht',
          type: 'send_message',
          next_step: null,
          start_step: true,
          arrow: {
            from: new Arrow({
              id: ids,
              fromItemX: 10200,
              fromItemY: 9800
            }),
            to: new Arrow({id: null})
          }
        }));
        this._builderService.requestDataItems = this._builderService.requestDataAll.draftItems;
        this._builderService.updateDraftItem();
      }
      this.preloader = false;
      this._sharedService.preloaderView = false;
    } catch (err) {
      this._sharedService.showRequestErrors(err);
    }
  }

  /**
   * Save broadcast
   * @returns {Promise<void>}
   */
  public async savedBroadcast(): Promise<any> {
    const data = {
      targeting: this._sharedService.conditionArray,
      type: this.broadcastType,
      tag: (this.broadcastType === 1) ? this.broadcastTag : null,
      pushType: this.broadcastPushType
    };
    try {
      await this._userService.saveBroadcast(this.pageId, this.currentPostId, data);
      this._toastr.success('Newsletter speichern');
    } catch (err) {
      this._sharedService.showRequestErrors(err);
    }
  }

  public saveWithBroadcast() {
    this.broadcastTypeCheck = !this.broadcastTypeCheck;
  }

  /**
   * Publish broadcast
   * @returns {Promise<void>}
   */
  public async publishedBroadcast() {
    let valid = true;
    const date = new Date();
    let dateInput;
    if (this.dateTime && this.dateTime.nativeElement) {
      dateInput = new Date(this.dateTime.nativeElement.value);
    }
    if (this.broadcastSendType === 1) {
      dateInput = null;
    } else if (this.broadcastSendType === 2) {
      if (!dateInput || date > dateInput) {
        valid = false;
        this._toastr.error('Wählen Sie das gültige Datum und die Uhrzeit aus');
      }
    }
    if (this.broadcastType === 1 && !this.broadcastTag) {
      valid = false;
      this._toastr.error('Wählen Sie einen Tag');
    }
    if (valid) {
      if (this.broadcastType === 1 && !this.broadcastTypeCheck) {
        this.openVerticallyCentered(this.content);
      } else {
        const data = {
          targeting: this._sharedService.conditionArray,
          type: this.broadcastType,
          tag: (this.broadcastType === 1) ? this.broadcastTag : null,
          pushType: this.broadcastPushType,
          sendType: this.broadcastSendType,
          sendDate: dateInput
        };
        try {
          await this._userService.publishBroadcast(this.pageId, this.currentPostId, data);
          this.publish = true;
          this.modalActiveClose.dismiss();
        } catch (err) {
          this._sharedService.showRequestErrors(err);
        }
      }
    }
  }

  /**
   * Choise broadcast tabs
   * @param value {string}
   */
  public choiceTab(value) {
    this.openTab = value;
  }

  /**
   * Save flow item
   * @param func {string}
   * @returns {Promise<void>}
   */
  public async saveFlowItem(func): Promise<any> {
    const rest = this._builderService.requestDataItems;
    let valid = 0;
    valid += this._sharedService.validationRequaredWidgetFields(rest);
    if (valid > 0) {
      this._builderService.validateError = true;
      this._builderService.requestDataSidebar =
        this._builderService.requestDataItems.find((item) => item.uuid === this._sharedService.validMainIds[0]);
      this._builderService.config = this._builderService.requestDataSidebar;
      this._builderService.openSidebar = true;

      this.openTab = 'flow';
      this._toastr.error(this._sharedService.validErrorsArray[0]);
    } else {
      this._builderService.validateError = false;
      const data = {
        items: rest
      };
      try {
        await this._userService.saveFlowItem(this._userService.userID , this._sharedService.flowId, data);
        if (func === 'save') {
          this.savedBroadcast();
        } else if (func === 'publish') {
          this.publishedBroadcast();
        }
        this._builderService.counterUpdate = 0;
      } catch (err) {
        this._sharedService.showRequestErrors(err);
      }
    }
  }

  /**
   * Sekect condition item
   */
  public activeConditionSelect() {
    setTimeout(() => {
      this._sharedService.noneConditionButton = true;
      this._sharedService.activeCondition = !this._sharedService.activeCondition;
    }, 100);
  }

  /**
   * Save broadcast settings
   * @param value {number}
   * @param key {string}
   */
  public saveBroadcastSettings(value, key) {
    this[key] = Number(value);
  }

  /**
   * Open popup
   * @param content
   */
  public openVerticallyCentered(content) {
    this.modalActiveClose = this._modalService.open(content);
    this.modalActiveClose.result.then((res) => {
      this.broadcastTypeCheck = false;
    }, (data) => {
      this.broadcastTypeCheck = false;
    })
  }

}
