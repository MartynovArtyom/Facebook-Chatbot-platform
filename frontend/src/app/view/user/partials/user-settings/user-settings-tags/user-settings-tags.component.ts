import { Component, OnInit } from '@angular/core';
import { UserService } from '../../../../../services/user.service';
import { SharedService } from '../../../../../services/shared.service';
import { NgbModal } from '@ng-bootstrap/ng-bootstrap';
import { ToastrService } from 'ngx-toastr';

@Component({
  selector: 'app-user-settings-tags',
  templateUrl: './user-settings-tags.component.html',
  styleUrls: ['./user-settings-tags.component.scss']
})
export class UserSettingsTagsComponent implements OnInit {
  public preload = true;
  public modalActiveClose: any;
  public tags = [];
  public tagName = '';

  constructor(
    private readonly _sharedService: SharedService,
    private readonly _modalService: NgbModal,
    private readonly _toastr: ToastrService,
    private readonly _userService: UserService
  ) { }

  ngOnInit() {
    this.getTags();
  }

  /**
   * Get all tags
   * @returns {Promise<void>}
   */
  public async getTags(): Promise<any> {
    try {
      const data = await this._userService.getTags(this._userService.userID);
      this.preload = false;
      this.tags = data;
    } catch (err) {
      this._sharedService.showRequestErrors(err);
    }
  }

  /**
   * Outside tag
   * @param tag {object}
   */
  public outsideTags(tag) {
    delete tag['active'];
  }

  /**
   * Show edit tag
   * @param tag {object}
   */
  public showEditTag(tag) {
    tag['active'] = true;
  }

  /**
   * Update tag name
   * @param tag {object}
   * @returns {Promise<void>}
   */
  public async setValueTag(tag): Promise<any> {
    if (tag.name) {
      try {
        await this._userService.updateTag(this._userService.userID, tag.id, {name: tag.name});
        this._toastr.success('Tag aktualisiert');
      } catch (err) {
        this._sharedService.showRequestErrors(err);
      }
    }
  }

  /**
   * Open popup
   * @param content {any}
   */
  public openVerticallyCenter(content) {
    this.modalActiveClose = this._modalService.open(content);
    this.tagName = '';
  }

  /**
   * Create new tag
   * @returns {Promise<void>}
   */
  public async createNewTag(): Promise<any> {
    if (this.tagName) {
      try {
        const tag = await this._userService.createTag(this._userService.userID, this.tagName);
        this.tags.push(tag);
        this._toastr.success('Tag erstellt');
        this.modalActiveClose.dismiss();
      } catch (err) {
        this._sharedService.showRequestErrors(err);
      }
    }

  }

  /**
   * Delete tag
   * @param tag {object}
   * @param index {number}
   * @returns {Promise<void>}
   */
  public async deleteTag(tag, index): Promise<any> {
    try {
      await this._userService.deleteTag(this._userService.userID, tag.id);
      this.tags.splice(index, 1);
      this._toastr.success('Tag entfernt');
    } catch (err) {
      this._sharedService.showRequestErrors(err);
    }
  }
}
