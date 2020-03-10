import {Component, ElementRef, Input, OnInit, ViewChild} from '@angular/core';
import { Item } from '../../builder/builder-interface';
import { BuilderFunctionsService } from '../../services/builder-functions.service';
import { BuilderService } from '../../services/builder.service';
import { UserService } from '../../../../services/user.service';
import { ToastrService } from 'ngx-toastr';
import { SharedService } from '../../../../services/shared.service';
import { ActivatedRoute, Router } from '@angular/router';
import { CompanyManagerService } from '../../../../services/company-manager.service';
import { NgbModal } from '@ng-bootstrap/ng-bootstrap';
import { FormControl, FormGroup, Validators } from '@angular/forms';

@Component({
  selector: 'app-perform-actions-items',
  templateUrl: './perform-actions-items.component.html',
  styleUrls: [
    '../../assets/general-style.scss',
    './perform-actions-items.component.scss'
  ]
})
export class PerformActionsItemsComponent implements OnInit {
  @ViewChild('contentFlow') public contentFlow: ElementRef;
  @ViewChild('contentFlowSub') public contentFlowSub: ElementRef;

  @Input() config: Item;

  public addTag = [];
  public subscribeSequence = [];
  public customField = [];
  public cf = [];
  public queryParams: any;
  public modalActiveClose: any;
  public selectAction: any;
  public newCustomFieldForm: FormGroup;

  constructor(
    public _builderFunctionsService: BuilderFunctionsService,
    public _userService: UserService,
    public _toastr: ToastrService,
    public companyManagerService: CompanyManagerService,
    private _route: ActivatedRoute,
    private _router: Router,
    public _sharedService: SharedService,
    public _modalService: NgbModal,
    public _builderService: BuilderService
  ) {
    this._route.queryParams.subscribe(data => {
      this.queryParams = data;
    });
  }

  ngOnInit() {
    this._builderService.addTag.forEach((item) => {
      this.addTag.push(item.name);
    });
    this._builderService.subscribeSequence.forEach((item) => {
      this.subscribeSequence.push(item.name);
    });
    this.cf = this._builderService.customField;
    this._builderService.customField.forEach((item) => {
      this.customField.push(item.name);
    });

    this.newCustomFieldForm = new FormGroup({
      name: new FormControl('', [Validators.required, Validators.minLength(1)]),
      type: new FormControl(1),
      description: new FormControl('')
    });
  }

  /**
   * Clear action id
   * @param action {object}
   */
  public clearID(action) {
    action.id = null;
    action.value = null;
    this._builderService.updateDraftItem();
  }

  /**
   * Create tag or select value to tag
   * @param value {string}
   * @param action {object}
   * @returns {Promise<void>}
   */
  public async createTag(value, action) {
    if (value) {
      let counts = 0;
      this.addTag.forEach((item) => {
        if (item === value) {
          counts++;
        }
      });

      if (counts === 0) {

        try {
          const response = await this._userService.createTag(this._userService.userID, value);
          this._builderService.addTag.push({
            tagID: response['id'],
            name: response['name']
          });
          this._toastr.success('Tag erstellt');
          this.addTag.push(value);
          action.id = response['id'];
          this._builderService.updateDraftItem();
        } catch (err) {
          this._sharedService.showRequestErrors(err);
        }
      } else {
        this.triggerUpdate(action, value);
      }
    } else {
      this.triggerUpdate(action, value);
    }
  }

  /**
   * Create tag or select value to tag
   * @param value {string}
   * @param action {object}
   * @returns {Promise<void>}
   */
  public async createNewAutoresponder(value, action) {
    if (value) {
      let counts = 0;
      this.subscribeSequence.forEach((item) => {
        if (item === value) {
          counts++;
        }
      });

      if (counts === 0) {
        const data = {
          title: value
        };

        try {
          const response = await this._userService.createSequence(this._userService.userID, data);
          this._builderService.subscribeSequence.push({
            sequenceID: response['id'],
            name: response['title']
          });
          this._toastr.success('Sequenz erstellt');
          this.subscribeSequence.push(value);
          action.id = response['id'];
          action['value'] = response['title'];
          this._builderService.updateDraftItem();

          if (this.queryParams.view && this.queryParams.view === 'flowEdit') {
            this.companyManagerService.navigateToTab(response, 'autoresponder', true);
          }
        } catch (err) {
          action.id = null;
          action.value = null;
          this._sharedService.showRequestErrors(err);
        }
      } else {
        this.triggerUpdate(action, value);
      }
    } else {
      this.triggerUpdate(action, value);
    }
  }

  /**
   * Create new custom field
   * @param value {string}
   * @param action {object}
   * @returns {Promise<any>}
   */
  public async createNewCustomFiled(value, action): Promise<any> {
    if (value) {
      let counts = 0;
      this.customField.forEach((item) => {
        if (item === value) {
          counts++;
        }
      });

      if (counts === 0) {
        this.newCustomFieldForm.patchValue({
          name: value,
          type: 1,
          description: ''
        });
        this.selectAction = action;
        this.modalActiveClose = this._modalService.open(this.contentFlow);
      } else {
        this.triggerUpdate(action, value);
      }
    } else {
      this.triggerUpdate(action, value);
    }
  }

  /**
   * Create new custom field
   * @param value {string}
   * @param action {object}
   * @returns {Promise<any>}
   */
  public async createNewSubCF(value, action): Promise<any> {
    if (value) {
      let counts = 0;
      this.customField.forEach((item) => {
        if (item === value.name) {
          counts++;
        }
      });

      if (counts === 0) {
        this.newCustomFieldForm.patchValue({
          name: value.name,
          type: 1,
          description: ''
        });
        this.selectAction = action;
        this.modalActiveClose = this._modalService.open(this.contentFlowSub);
      } else {
        this.selectCustomField(value, action);
      }
    } else {
      this.selectCustomField(value, action);
    }
  }

  /**
   * Create CF
   */
  public async createCF(): Promise<any> {

    const form = {
      name: this.newCustomFieldForm.controls['name'].value,
      type: Number(this.newCustomFieldForm.controls['type'].value),
      description: this.newCustomFieldForm.controls['description'].value
    };
    if (this.newCustomFieldForm.controls['name'].value && this.newCustomFieldForm.controls['type'].value) {
      try {
        const data = await this._userService.createCustomFields(this._userService.userID, form);
        this._toastr.success('Benutzerdefiniertes Feld erstellt');
        this.customField.push(data.name);
        this.cf.push({
          customFieldID: data.id,
          name: data.name,
          type: data.type
        });
        this.selectAction.id = data.id;
        this.modalActiveClose.dismiss();

        this._builderService.updateDraftItem();
        this.newCustomFieldForm.reset();
      } catch (err) {
        this._sharedService.showRequestErrors(err);
      }
    } else {
      this._toastr.error('Bitte füllen Sie alle Felder aus');
    }
  }

  /**
   * Create CF
   */
  public async createCFSub(): Promise<any> {

    const form = {
      name: this.newCustomFieldForm.controls['name'].value,
      type: Number(this.newCustomFieldForm.controls['type'].value),
      description: this.newCustomFieldForm.controls['description'].value
    };
    if (this.newCustomFieldForm.controls['name'].value && this.newCustomFieldForm.controls['type'].value) {
      try {
        const data = await this._userService.createCustomFields(this._userService.userID, form);
        this._toastr.success('Benutzerdefiniertes Feld erstellt');
        this.customField.push(data.name);
        this.cf.push({
          customFieldID: data.id,
          name: data.name,
          type: data.type
        });
        this.selectAction.id = data['id'];
        this.modalActiveClose.dismiss();

        this.selectAction['custom_field'] = JSON.parse(JSON.stringify(data));
        this.selectAction['value'] = null;
        this.selectAction['custom_field_option']['type'] = '1';
        this._builderService.updateDraftItem();
        this.newCustomFieldForm.reset();
      } catch (err) {
        this._sharedService.showRequestErrors(err);
      }
    } else {
      this._toastr.error('Bitte füllen Sie alle Felder aus');
    }
  }

  /**
   * Open autoresponder
   * @param action {object}
   */
  public openAutoresponder(action) {
    const data = {
      title: action.value,
      id: action.id
    };
    if (this.queryParams.view && this.queryParams.view === 'flow' ||
        this.queryParams.view && this.queryParams.view === 'flowEdit') {
      this.companyManagerService.navigateToTab(data, 'autoresponder', true);
    }
  }

  /**
   * Select custom filed value
   * @param customFields {object}
   * @param item {object}
   */
  public selectCustomField(customFields, item) {
    if (customFields) {
      item['custom_field'] = JSON.parse(JSON.stringify(customFields));
      item['value'] = null;
      item['custom_field_option']['type'] = '1';
      item['id'] = JSON.parse(JSON.stringify(customFields.customFieldID));
    } else {
      item['custom_field'] = {};
      item['value'] = null;
      item['custom_field_option']['type'] = '1';
      item['id'] = null;
    }
    this._builderService.updateDraftItem();
  }

  /**
   * Set custom filed option
   * @param action {object}
   * @param value {string}
   */
  public setCustomFieldOption(action, value) {
    action['custom_field_option']['type'] = value;
    this._builderService.updateDraftItem();
  }

  /**
   * Set custom field option date
   * @param action {object}
   * @param value {string}
   */
  public setCustomFieldOptionDate(action, value) {
    action['custom_field_option']['type'] = value;
    if (action['custom_field_option']['type'] === '2') {
      action['value'] = 'the date of the action';
    } else {
      action['value'] = null;
    }
    this._builderService.updateDraftItem();
  }

  /**
   * Trigger update actions
   * @param action {object}
   * @param value {string}
   */
  public triggerUpdate (action, value) {
    if (action.type === 'add_tag' || action.type === 'remove_tag') {
      this._builderService.addTag.forEach((item) => {
        if (item.name === value) {
          action.id = item.tagID;
        }
      });
    }

    if (action.type === 'subscribe_sequence' || action.type === 'unsubscribe_sequence') {
      this._builderService.subscribeSequence.forEach((item) => {
        if (item.name === value) {
          action.id = item.sequenceID;
        }
      });
    }
    if (action.type === 'clear_subscriber_custom_field') {
      this._builderService.customField.forEach((item) => {
        if (item.name === value) {
          action.id = item.customFieldID;
        }
      });
    }
    this._builderService.updateDraftItem();
  }

  /**
   * Save boolean value custom filed
   * @param item {object}
   * @param key {number}
   * @param value {string}
   */
  public saveBooleanAction(item, key, value) {
    item['value'] = value;
    this._builderService.updateDraftItem();
  }

}
