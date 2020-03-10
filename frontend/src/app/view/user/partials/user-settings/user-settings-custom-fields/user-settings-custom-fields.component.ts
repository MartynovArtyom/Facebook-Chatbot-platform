import { Component, ElementRef, OnInit, ViewChild } from '@angular/core';
import { UserService } from '../../../../../services/user.service';
import { SharedService } from '../../../../../services/shared.service';
import { FormControl, FormGroup, Validators } from '@angular/forms';
import { NgbModal } from '@ng-bootstrap/ng-bootstrap';
import { ToastrService } from 'ngx-toastr';

@Component({
  selector: 'app-user-settings-custom-fields',
  templateUrl: './user-settings-custom-fields.component.html',
  styleUrls: ['./user-settings-custom-fields.component.scss']
})
export class UserSettingsCustomFieldsComponent implements OnInit {
  @ViewChild('name') public name: ElementRef;
  @ViewChild('description') public description: ElementRef;

  public newCustomFieldForm: FormGroup;
  public customFieldsArray = [];
  public customFieldsArchiveArray = [];
  public modalActiveClose: any;
  public preload = true;
  public archiveCheck = false;

  constructor(
    private readonly _sharedService: SharedService,
    private readonly _modalService: NgbModal,
    private readonly _userService: UserService,
    private _toastr: ToastrService
  ) { }

  ngOnInit() {
    this.newCustomFieldForm = new FormGroup({
      name: new FormControl('', [Validators.required, Validators.minLength(1)]),
      type: new FormControl(1),
      description: new FormControl('')
    });

    this.getCustomFields();
  }

  /**
   * Get custom fields
   * @returns {Promise<void>}
   */
  public async getCustomFields(): Promise<void> {
    try {
      const data = await this._userService.getCustomFields(this._userService.userID);
      data.forEach((item) => {
        if (item.status) {
          this.customFieldsArray.push(item);
        } else {
          this.customFieldsArchiveArray.push(item);
        }
      });
      this.preload = false;
    } catch (err) {
      this._sharedService.showRequestErrors(err);
    }
  }

  /**
   * Create custom field
   * @returns {Promise<void>}
   */
  public async createCustomFields(): Promise<void> {
    const form = {
      name: this.newCustomFieldForm.controls['name'].value,
      type: Number(this.newCustomFieldForm.controls['type'].value),
      description: this.newCustomFieldForm.controls['description'].value
    };
    if (this.newCustomFieldForm.controls['name'].value && this.newCustomFieldForm.controls['type'].value) {
      try {
        const data = await this._userService.createCustomFields(this._userService.userID, form);
        this._toastr.success('Benutzerdefiniertes Feld erstellt');
        this.customFieldsArray.push(data);
        this.newCustomFieldForm.reset();
        this.modalActiveClose.dismiss();
      } catch (err) {
        this._sharedService.showRequestErrors(err);
      }
    } else {
      this._toastr.error('Bitte füllen Sie alle Felder aus');
    }
  }

  /**
   * Add to custom fileds
   * @param customFieldArchive {object}
   * @param value {boolean}
   */
  public async addCustomField(customFieldArchive, value): Promise<void> {
    this.archiveCheck = true;
    customFieldArchive.status = true;
    try {
      await this._userService.updateCustomField(this._userService.userID, customFieldArchive.id, {status: true});
      this.customFieldsArray.push(customFieldArchive);
      const index = this.customFieldsArchiveArray.indexOf(customFieldArchive);
      this.customFieldsArchiveArray.splice(index, 1);
      this._toastr.success('Benutzerdefiniertes Feld nicht archiviert');
      this.archiveCheck = false;
    } catch (err) {
      this._sharedService.showRequestErrors(err);
    }
  }

  /**
   * Add to archive custom filed
   * @param customField {object}
   * @param value {boolean}
   */
  public async addCustomFieldArchive(customField, value): Promise<void> {
    this.archiveCheck = true;
    customField.status = false;
    try {
      await this._userService.updateCustomField(this._userService.userID, customField.id, {status: false});
      this.customFieldsArchiveArray.push(customField);
      const index = this.customFieldsArray.indexOf(customField);
      this.customFieldsArray.splice(index, 1);
      this._toastr.success('Benutzerdefiniertes Feld archiviert');
      this.archiveCheck = false;
    } catch (err) {
      this._sharedService.showRequestErrors(err);
    }
  }

  /**
   * Open popup
   * @param content {any}
   */
  public openVerticallyCenter(content) {
    this.modalActiveClose = this._modalService.open(content);
    this.newCustomFieldForm.patchValue({
      name: '',
      type: 1,
      description: ''
    });
  }

  /**
   * Show edit custom field
   * @param item {object}
   */
  public showEditCustomField(item) {
    if (this.archiveCheck === false) {
      item['active'] = true;
    }
  }

  /**
   * Hide edit custom field
   * @param item {object}
   */
  public outsideShowEditCustomField(item) {
    delete item['active'];
  }

  /**
   * Edit custom field
   * @param item {object}
   */
  public editCustomField(item) {
    const data = {
      name: this.name.nativeElement.value,
      description: this.description.nativeElement.value
    };
    setTimeout(() => {
      try {
        this._userService.updateCustomField(this._userService.userID, item.id, data);
        item.name = data.name;
        item.description = data.description;
        this.outsideShowEditCustomField(item);
        this._toastr.success('Benutzerdefiniertes Feld gespeichert');
      } catch (err) {
        this._sharedService.showRequestErrors(err);
      }
    }, 100);
  }

}
