import { Component, OnInit } from '@angular/core';
import { SharedService } from '../../../../../services/shared.service';
import { UserService } from '../../../../../services/user.service';
import { ActivatedRoute } from '@angular/router';
import { UserPostItem } from '../../../../../../entities/models-user';
import { ToastrService } from 'ngx-toastr';
import { AudienceService } from '../../../../../services/audience.service';

@Component({
  selector: 'app-user-autoposting-channel',
  templateUrl: './user-autoposting-channel.component.html',
  styleUrls: ['./user-autoposting-channel.component.scss']
})
export class UserAutopostingChannelComponent implements OnInit {

  public preloader = true;
  public currentPostId: any;
  public postItem = new UserPostItem({});

  constructor(
    public readonly _userService: UserService,
    public readonly _route: ActivatedRoute,
    public readonly _sharedService: SharedService,
    public readonly _audienceService: AudienceService,
    private readonly _toastr: ToastrService
  ) { }

  ngOnInit() {
    this._sharedService.conditionArray = [];
    this.currentPostId = this._route.snapshot.params['id'];
    this._audienceService.getSubscribersFilter().then(() => {
      this.getAutopostingById();
    });
  }

  /**
   * Change post item option
   * @param option {string}
   * @param value {string}
   */
  public changePostItem(option, value) {
    if (value === 'true') {
      value = true;
    } else if (value === 'false') {
      value = false;
    } else if (option === 'typePush') {
      value = Number(value);
    }

    this.postItem[option] = value;
  }

  /**
   * Save autoposting
   * @returns {Promise<void>}
   */
  public async savePostItem(): Promise<void> {
    let filter;
    if (this._sharedService.conditionArray.length === 0) {
      filter = null;
    } else {
      filter = this._sharedService.conditionArray;
    }
    const data = {
      title: this.postItem.title,
      status: this.postItem.status,
      typePush: this.postItem.typePush,
      targeting: filter
    };

    try {
      await this._userService.savePostItem(this._userService.userID , this.currentPostId, data);
      this._toastr.success('Kanal gespeichert');
    } catch (err) {
      this._sharedService.showRequestErrors(err);
    }
  }

  /**
   * Get autoposting by id
   * @returns {Promise<void>}
   */
  public async getAutopostingById(): Promise<any> {
    try {
      this.postItem = await this._userService.getAutopostingById(this._userService.userID , this.currentPostId);
      if (this.postItem.targeting) {
        this._sharedService.conditionArray = this.postItem.targeting;
      }
      this.preloader = false;
      this._sharedService.preloaderView = false;
    } catch (err) {
      this._sharedService.showRequestErrors(err);
    }
  }

  /**
   * Sekect condition item
   */
  public activeConditionSelect() {
    /* откоментировать код*/
    this._sharedService.dataWidget = {};
    /* откоментировать код*/
    setTimeout(() => {
      this._sharedService.noneConditionButton = true;
      this._sharedService.activeCondition = !this._sharedService.activeCondition;
    }, 100);
  }

}
