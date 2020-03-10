import { Component, Input, OnInit, ViewChild } from '@angular/core';
import { NgbModal } from '@ng-bootstrap/ng-bootstrap';
import { WidgetById } from '../../../../../../../entities/models-user';
import { ActivatedRoute, Router } from '@angular/router';
import { ToastrService } from 'ngx-toastr';
import { UserService } from '../../../../../../services/user.service';
import { SharedService } from '../../../../../../services/shared.service';
import { CompanyManagerService } from '../../../../../../services/company-manager.service';
import { IPost } from '../../../../../../interfaces/user.interface';

@Component({
  selector: 'app-growth-tools',
  templateUrl: './growth-tools.component.html',
  styleUrls: ['./growth-tools.component.scss']
})
export class GrowthToolsComponent implements OnInit {
  @ViewChild('desc') public desc;
  public itemPanel = {
    params: {
      description: '',
      activeEmoji: false,
      activeAction: false
    }
  };
  public _viewId: any;
  @Input('viewId') set viewId(viewId) {
    if (viewId) {
      this._viewId = viewId;
    }
  }
  get viewId() {
    return this._viewId;
  }

  public web = null;
  public currentId: any;
  public preloader = true;
  public previewCheck = true;
  public widgetConfig: any;
  public modalActiveClose: any;
  public imageMessengerCode: string;
  public refUrl: string;
  public widgetById: WidgetById;
  public tabsValue = 'initial_state';
  public previewMobile = true;
  public changeRef = false;
  public dataJSON: any;

  public bulkActions = [
    {
      name: 'User Id',
      desc: '{{user_id}} '
    },
    {
      name: 'Page Id',
      desc: '{{page_id}} '
    },
    {
      name: 'Page Name',
      desc: '{{page_name}} '
    },
    {
      name: 'First Name',
      desc: '{{user_first_name}} '
    },
    {
      name: 'Last Name',
      desc: '{{user_last_name}} '
    },
    {
      name: 'Full Name',
      desc: '{{user_full_name}} '
    },
    {
      name: 'Gender',
      desc: '{{user_gender}} '
    },
    {
      name: 'Locale',
      desc: '{{user_locale}} '
    },
    {
      name: 'Language',
      desc: '{{user_language}} '
    }
  ];
  public customField = [];
  public bulkActionsCF = [];

  public publishPosts: IPost[] = [];
  public schedulePosts: IPost[] = [];
  public promotionPosts: IPost[] = [];
  public postPreloader = false;
  public checkConfirmPostSearch = false;
  public searchP = null;
  public checkingTabs = {
    publish: false,
    schedule: false,
    promotion: false
  };

  constructor(
    public readonly _userService: UserService,
    public readonly _route: ActivatedRoute,
    public readonly _router: Router,
    public readonly _sharedService: SharedService,
    public readonly _modalService: NgbModal,
    public readonly companyManagerService: CompanyManagerService,
    private readonly _toastr: ToastrService
  ) {
  }

  ngOnInit() {
    this.currentId = this._viewId;
    this.getSubscriberActions().then(() => {
      this.widgetConfig = null;
      this.getWidgetById();
    });
  }

  /**
   * Get subscriber actions
   * @returns {Promise<void>}
   */
  public async getSubscriberActions(): Promise<any> {
    try {
      const data = await this._userService.getSubscribersAction();
      this.customField = data.customFields;
      this.createBulkActions();
    } catch (err) {
      this._sharedService.showRequestErrors(err);
    }
  }

  /**
   * Create builk actions array
   */
  public createBulkActions() {
    this.bulkActionsCF = [];
    this.customField.forEach((item) => {
      this.bulkActionsCF.push({
        name: item.name,
        desc: '{{cf_' + item.customFieldID + '}}'
      });
    });
  }

  public changeRefUrl(customRef) {
    this.changeRef = true;
  }

  /**
   * Edit widget status
   * @returns {Promise<any>}
   */
  public editWidgetStatus(status) {
    if (this.widgetById.details.type === 11 && this.widgetById.details.status) {
      if (!this.widgetById.details.postId &&
          this.widgetById.details.status ||
          !this.widgetById.details.postId &&
          !status) {
        if (status && this.widgetById.details.status) {
          setTimeout(() => {
            this.widgetById.details.status = false;
            this._toastr.error('Bitte wählen Sie einen Beitrag für die Kommentarverfolgung');
          }, 100);
        } else if (!status) {
          this._toastr.error('Bitte wählen Sie einen Beitrag für die Kommentarverfolgung');
        }
      } else {
        if (!this.widgetConfig.confirm_post &&
            this.widgetById.details.status ||
            !this.widgetConfig.confirm_post &&
            !status) {
          if (status && this.widgetById.details.status) {
            setTimeout(() => {
              this.widgetById.details.status = false;
              this._toastr.error('Bitte bestätigen Sie, dass der ausgewählte Beitrag ausdrücklich besagt, dass diese Seite Benutzer' +
                ' kontaktieren wird, die über Messenger Kommentare abgeben.');
            }, 100);
          } else if (!status) {
            this._toastr.error('Bitte bestätigen Sie, dass der ausgewählte Beitrag ausdrücklich besagt, dass diese Seite Benutzer' +
              ' kontaktieren wird, die über Messenger Kommentare abgeben.');
          }
        } else {
          if (!this.widgetConfig.message &&
              this.widgetById.details.status ||
              !this.widgetConfig.message &&
              !status) {
            if (status && this.widgetById.details.status) {
              setTimeout(() => {
                this.widgetById.details.status = false;
                this._toastr.error('Bitte geben Sie eine Nachricht ein');
              }, 100);
            } else if (!status) {
              this._toastr.error('Bitte geben Sie eine Nachricht ein');
            }
          } else if (this.widgetConfig.sending_options === 3 &&
                     !this.widgetConfig.repeat_keywords &&
                     this.widgetById.details.status ||
                     this.widgetConfig.sending_options === 3 &&
                     !this.widgetConfig.repeat_keywords &&
                     !status) {
            if (status && this.widgetById.details.status) {
              setTimeout(() => {
                this.widgetById.details.status = false;
                this._toastr.error('Bitte geben Sie Stichwörter ein');
              }, 100);
            } else if (!status) {
              this._toastr.error('Bitte geben Sie Stichwörter ein');
            }
          } else {
            if (status) {
              this.chooseStatus();
              if (this.widgetById.details.status) {
                this.editWidget();
              }
            } else {
              this.editWidget();
            }
          }
        }
      }
    } else {
      (status) ? this.chooseStatus() : this.editWidget();
    }
  }

  public async chooseStatus(): Promise<any> {
    const data = {
      status: this.widgetById.details.status
    };

    try {
      await this._userService.updateWidgetStatus(this._userService.userID, this.widgetById.details.id, data);
    } catch (err) {
      this._sharedService.showRequestErrors(err);
    }
  }

  /**
   * Render preview widget
   * @param value {boolean}
   */
  public renderPreview(value) {
    this.previewCheck = value;
  }

  /**
   * Come to next/prev tab
   * @param tab {string}
   */
  public comeToTabs(tab) {
    this.tabsValue = tab;
  }

  /**
   * Get widgets
   * @returns {Promise<void>}
   */
  public async getWidgetById(): Promise<any> {
    try {
      this.widgetById = await this._userService.getWidgetById(this._userService.userID, this.currentId);
      if (!this.widgetById.details.options || this.widgetById.details.options.length === 0) {
        switch (this.widgetById.details.type) {
          case 1:
            this.widgetConfig = JSON.parse(JSON.stringify(this._sharedService.defaultBar));
            break;
          case 2:
            this.widgetConfig = JSON.parse(JSON.stringify(this._sharedService.defaultSlideIn));
            break;
          case 3:
            this.widgetConfig = JSON.parse(JSON.stringify(this._sharedService.defaultPopup));
            break;
          case 4:
            this.widgetConfig = JSON.parse(JSON.stringify(this._sharedService.defaultPageTakeover));
            break;
          case 5:
            this.widgetConfig = JSON.parse(JSON.stringify(this._sharedService.defaultButton));
            break;
          case 6:
            this.widgetConfig = JSON.parse(JSON.stringify(this._sharedService.defaultBox));
            break;
          case 7:
            this.tabsValue = 'setup';
            this.widgetConfig = JSON.parse(JSON.stringify(this._sharedService.defaultRefUrl));
            this.getWidgetRefUrl();
            break;
          case 8:
            this.tabsValue = 'setup';
            this.getWidgetJSON();
            break;
          case 9:
            this.tabsValue = 'setup';
            this.widgetConfig = JSON.parse(JSON.stringify(this._sharedService.defaultMessengerCode));
            this.getWidgetMessengerCode();
            break;
          case 10:
            this.widgetConfig = JSON.parse(JSON.stringify(this._sharedService.defaultChat));
            this.tabsValue = 'setup';
            break;
          case 11:
            this.widgetConfig = JSON.parse(JSON.stringify(this._sharedService.defaultComments));
            this.tabsValue = 'setup';
            break;
          default:
            break;
        }
      } else {
        this.widgetConfig = this.widgetById.details.options;
        if (this.widgetById.details.type === 7) {
          this.tabsValue = 'setup';
          this.getWidgetRefUrl();
        } else if (this.widgetById.details.type === 8) {
          this.tabsValue = 'setup';
          this.getWidgetJSON();
        } else if (this.widgetById.details.type === 9) {
          this.tabsValue = 'setup';
          this.getWidgetMessengerCode();
        } else if (this.widgetById.details.type === 10) {
          this.tabsValue = 'setup';
        } else if (this.widgetById.details.type === 11) {
          this.tabsValue = 'setup';
        }
      }
      this.preloader = false;
      this._sharedService.preloaderView = false;
    } catch (err) {
      this._sharedService.showRequestErrors(err);
    }
  }

  /**
   * Set image size
   */
  public setImageSize() {
    if (this.widgetConfig.image_size < 100) {
      this.widgetConfig.image_size = 100;
    } else if (this.widgetConfig.image_size > 2000) {
      this.widgetConfig.image_size = 2000;
    }
    this.getWidgetMessengerCode();
  }

  /**
   * Get widget json
   * @returns {Promise<void>}
   */
  public async getWidgetJSON(): Promise<any> {
    try {
      const data = await this._userService.getWidgetJSON(this._userService.userID, this.currentId);
      if (data.result) {
        this.dataJSON = {
          result: true,
          adsJSON: JSON.stringify(data.adsJSON, undefined, 4)
        };
      } else {
        this.dataJSON = data;
      }

    } catch (err) {
      this._sharedService.showRequestErrors(err);
    }
  }

  /**
   * Get widget ref url
   * @returns {Promise<void>}
   */
  public async getWidgetRefUrl(): Promise<any> {
    try {
      const data = await this._userService.getWidgetRefUrl(this._userService.userID, this.currentId);
      this.refUrl = data.refUrl;
    } catch (err) {
      this._sharedService.showRequestErrors(err);
    }
  }

  /**
   * Get widget messenger code
   * @returns {Promise<void>}
   */
  public async getWidgetMessengerCode(): Promise<any> {
    try {
      const data = await this._userService.getWidgetMessengerCode(this._userService.userID, this.currentId, this.widgetConfig.image_size);
      this.imageMessengerCode = data.url;
    } catch (err) {
      this._sharedService.showRequestErrors(err);
    }
  }

  /**
   * Change hide bar
   */
  public changeHideBar() {
    this.widgetConfig.hide_bar = !this.widgetConfig.hide_bar;
  }

  /**
   * Change show description
   */
  public changeShowDescription() {
    this.widgetConfig.show_desc = !this.widgetConfig.show_desc;
  }

  /**
   * Change show description
   */
  public changeShowDescriptionAfter() {
    this.widgetConfig.show_desc_after = !this.widgetConfig.show_desc_after;
  }

  /**
   * Update widget file
   * @param files {File}
   * @param key {string}
   * @returns {Promise<void>}
   */
  public async uploadWidgetFile(files, key) {
    if (files.length > 0) {
      const formData = new FormData();
      formData.append('file', files[0]);

      try {
        const response = await this._userService.uploadWidgetFile(this._userService.userID , formData);
        this.widgetConfig[key] = response.url;
      } catch (err) {
        this._sharedService.showRequestErrors(err);
      }
    }
  }

  /**
   * Delete widget image
   */
  public deleteWidgetImage() {
    this.widgetConfig.image = '';
  }

  /**
   * Change input fields
   * @param field {string}
   * @param value {string}
   */
  public changeInputFields(field, value) {
    this.widgetConfig[field] = value;
  }

  /**
   * Change input fields
   * @param field {string}
   * @param value {string}
   */
  public changeInputFieldsComments(field, value) {
    if (Number(value) < 1) {
      this.widgetConfig.delay[field] = 1;
    } else if (Number(value) > 60) {
      this.widgetConfig.delay[field] = 60;
    } else {
      this.widgetConfig.delay[field] = value;
    }
  }

  /**
   * Save edit widget
   * @returns {Promise<void>}
   */
  public async editWidget(): Promise<any> {
    const data = {
      options: this.widgetConfig,
      sequenceID: Number(this.widgetById.details.sequenceID),
      postId: this.widgetById.details.postId
    };

    try {
      const response = await this._userService.editWidget(this._userService.userID, this.widgetById.details.id, data);
      if (response && response.hasOwnProperty('refUrl')) {
        this.changeRef = false;
        this.refUrl = response.refUrl;
      }
      this._toastr.success('Kampagne gespeichert');
    } catch (err) {
      this._sharedService.showRequestErrors(err);
    }
  }

  /**
   * Open popup
   * @param content
   */
  public openVerticallyCentered(content) {
    this.modalActiveClose = this._modalService.open(content, {'size': 'lg'});
  }

  /**
   * Open popup
   * @param content
   */
  public openVerticallyCenterPosts(content) {
    this.checkingTabs = {
      promotion: false,
      publish: false,
      schedule: false
    };
    this.promotionPosts = [];
    this.schedulePosts = [];
    this.publishPosts = [];
    this.getFacebookPosts({nextId: 'publish'});
    this.searchP = null;
    this.checkConfirmPostSearch = false;
    this.modalActiveClose = this._modalService.open(content, {'size': 'lg'});
  }

  /**
   * Get FB posts
   * @param type {object}
   * @returns {Promise<any>}
   */
  public async getFacebookPosts(type): Promise<any> {
    if (type.nextId && !this.checkingTabs[type.nextId]) {
      await this.getPosts((type.nextId === 'publish') ? 1 : (type.nextId === 'schedule') ? 2 : (type.nextId === 'promotion') ? 3 : null,
        type.nextId + 'Posts');
      if (this[type.nextId + 'Posts']) {
        this[type.nextId + 'Posts'].forEach((item) => {
          if (item.id === this.widgetById.details.postId) {
            item.selected_post = true;
          }
        });
      }
      this.checkingTabs[type.nextId] = true;
      this.postPreloader = false;
    }
  }

  /**
   * Get posts
   * @param type {number}
   * @param array {array}
   * @returns {IPost[]}
   */
  public async getPosts(type, array): Promise<any> {
    if (type) {
      this.postPreloader = true;
      try {
        this[array] = await this._userService.getFbPosts(this._userService.userID, type);
      } catch (err) {
        this._sharedService.showRequestErrors(err);
      }
    }
  }

  /**
   * Search post
   * @param value {string}
   * @returns {Promise<any>}
   */
  public async searchPost(value): Promise<any> {
    if (value) {
      try {
        const post = await this._userService.searchPost(this._userService.userID, value);
        if (post) {
          this.widgetById.details.postId = post.id;
          this.widgetConfig.selected_post_item = post;
          this.modalActiveClose.dismiss();
        }
      } catch (err) {
        this._sharedService.showRequestErrors(err);
      }
    }
  }

  /**
   * Remove website
   * @param item {string}
   * @param array {array}
   * @param i {number}
   * @returns {Promise<void>}
   */
  public async removeWebsite(item, array, i): Promise<any> {
    const data = {
      website: item
    };
    try {
      await this._userService.removeWebsite(this._userService.userID, data);
      array.splice(i, 1);
    } catch (err) {
      this._sharedService.showRequestErrors(err);
    }
  }

  /**
   * Create website
   * @param value {string}
   * @param websiteArray {array}
   * @returns {Promise<void>}
   */
  public async createWebSite(value): Promise<any> {
    const objRE = /(?:http[s]?\/\/)?(?:[\w\-]+(?::[\w\-]+)?@)?(?:[\w\-]+\.)+(?:[a-z]{2,4})(?::[0-9]+)?(?:\/[\w\-\.%]+)*(?:\?(?:[\w\-\.%]+=[\w\-\.%!]+&?)+)?(#\w+\-\.%!)?/;
    const valid = objRE.test(value);

    if (valid) {
      const data = {
        website: value
      };
      try {
        const resp = await this._userService.createWebUrl(this._userService.userID, data);
        this._toastr.success('Domain hinzugefügt');
        this.widgetById.website = resp;
        this.web = null;
      } catch (err) {
        this._sharedService.showRequestErrors(err);
      }
    } else {
      this._toastr.error('Falsche Domain');
    }
  }

  /**
   * Edit widget title
   * @param value {string}
   * @returns {Promise<void>}
   */
  public async editWidgetTitle(value): Promise<any> {
    if (value) {
      const data = {
        name: value
      };
      try {
        await this._userService.updateWidget(this._userService.userID, this.currentId, data);
        this.widgetById.details.name = value;
        this.companyManagerService.dataFirstLevelTabs.forEach(item => {
          if (item.id === this.companyManagerService.activeCampaignItem.id &&
              item.type === this.companyManagerService.activeCampaignItem.type) {
            item.name = value;
          }
        });
      } catch (err) {
        this._sharedService.showRequestErrors(err);
      }
    } else {
      this._toastr.error('Geben sie den Kampagne ein');
    }
  }

  /**
   * Change after submit type
   * @param type {string}
   */
  public changeAterSubmitType(type) {
    this.widgetConfig.after_submit = type;
  }

  /**
   * Change open url type
   * @param value {string}
   */
  public changeOpenUrlType(value) {
    this.widgetConfig.open_this_url = value;
  }

  /**
   * Change device type
   * @param type {string}
   */
  public changeDeviceType(type) {
    this.widgetConfig.devices = type;
  }

  /**
   * Change device type
   * @param key {string}
   * @param type {string}
   */
  public changeType(key, type) {
    this.widgetConfig[key] = type;
  }

  /**
   * Set checkbox position
   * @param value {string}
   */
  public setCheckboxPosition(value) {
    this.widgetConfig.checkbox_position = value;
  }

  /**
   * Select image place
   * @param value {string}
   * @param key {string/number}
   */
  public selectImagePlace(value, key) {
    this.widgetConfig[key] = Number(value);
  }

  /**
   * Change width container
   * @param value {string}
   */
  public changeWidthContainer(value) {
    const width = Number(value);
    if (width < 280) {
      this.widgetConfig.width_container = 280;
    }
  }

  public setStatusCheck() {
    this.widgetConfig.confirm_post = !this.widgetConfig.confirm_post;
  }

  public fancyCount2(str) {
    const joiner = '\u{200D}';
    const split = str.split(joiner);
    let count = 0;

    for (const s of split) {
      const num = Array.from(s.split(/[\ufe00-\ufe0f]/).join('')).length;
      count += num;
    }

    return split;
  }

  public openSubTextareaPanel(item) {
    this.desc.nativeElement.focus();
    item['active'] = true;
  }

  public closeSubTextareaPanel(item) {
    item['active'] = false;
    delete item['activeAction'];
    delete item['activeEmoji'];
  }

  public openEmojiPanel(value) {
    this.itemPanel.params['activeEmoji'] = value;
    delete this.itemPanel.params['activeAction'];
  }

  public openBulkActionsPanel(value) {
    this.desc.nativeElement.focus();
    this.itemPanel.params['activeAction'] = value;
    delete this.itemPanel.params['activeEmoji'];
  }

  public getCaretPos(oField, action) {
    if (!this.desc.nativeElement.value || !this.widgetConfig['message']) {
      this.desc.nativeElement.value = action.desc;
    } else {
      this.desc.nativeElement.value = this.desc.nativeElement.value.substring(0, oField.selectionStart) +
        action.desc + this.desc.nativeElement.value.substring(oField.selectionStart, this.desc.nativeElement.value.length);
    }

    this.widgetConfig['message'] = this.desc.nativeElement.value;
    this.closeSubTextareaPanel(this.itemPanel.params);
    setTimeout(() => {
      this.desc.nativeElement.blur();
    }, 10);
  }

  /**
   * Close emoji
   * @param item {object}
   */
  public closeEmoji(item) {
    item = !item;
  }

  /**
   * Add emoji
   * @param oField {object}
   * @param $event {event}
   * @param item {object}
   * @param key {string}
   */
  public addEmoji(oField, $event, item, key) {
    if (!oField.value || !this.widgetConfig['message']) {
      oField.value = this.fancyCount2('' + $event + '');
    } else {
      oField.value = oField.value.substring(0, oField.selectionStart) +
        this.fancyCount2('' + $event + '') + oField.value.substring(oField.selectionStart, oField.value.length);
    }
    this.widgetConfig['message'] = oField.value;
  }

  /**
   * Set input value to object
   * @param object {object)
   * @param key {string}
   * @param value {string}
   */
  public setInputValue(object, key, value) {
    object[key] = value;
  }
}
