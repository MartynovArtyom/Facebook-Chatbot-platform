import { Component, ElementRef, OnInit, ViewChild } from '@angular/core';
import { SharedService } from '../../../../services/shared.service';
import { NgbModal } from '@ng-bootstrap/ng-bootstrap';
import { AudienceService } from '../../../../services/audience.service';
import { UserService } from '../../../../services/user.service';
import { ToastrService } from 'ngx-toastr';

@Component({
  selector: 'app-user-audience',
  templateUrl: './user-audience.component.html',
  styleUrls: ['./user-audience.component.scss']
})
export class UserAudienceComponent implements OnInit {

  @ViewChild('searchValue') public searchValue: ElementRef;

  public preloader = true;
  public conditionButton = false;
  public conditionInput = false;
  public usersCheck = true;
  public savedTagsCount = 5;
  public savedWidgetsCount = 5;
  public savedSequencesCount = 5;
  public modalActiveClose: any;
  public subscriberId: string;

  constructor(
    public readonly _sharedService: SharedService,
    public readonly _userService: UserService,
    public readonly _toastr: ToastrService,
    public readonly _audienceService: AudienceService,
    private readonly _modalService: NgbModal
  ) { }

  ngOnInit() {
    this._sharedService.openBulkActions = false;
    this._audienceService.subscriberFilterSystem = [];
    this._audienceService.subscriberFilterTags = [];
    this._audienceService.subscriberFilterWidgets = [];
    this._audienceService.subscriberFilterSequences = [];
    this._audienceService.subscriberFilterCustomFields = [];
    this._sharedService.conditionArray = [];
    this._audienceService.getSubscribersFilter().then(() => {
      this._audienceService.getSubscribers().then(() => {
        this.preloader = false;
      });
    });
  }

  /**
   * Show more subscribers
   */
  public showMoreSubscribers() {
    this._audienceService.countSubsc += 10;
  }

  /**
   * Open bulk actions options
   */
  public openActionBulk(keyword) {
    this._sharedService.openBulkActions = true;
  }

  /**
   * Search input focus out
   */
  public searchFocusOut() {
    if (!this._audienceService.searchFilterValue) {
      this.conditionInput = false;
    }
  }

  /**
   * Display condition
   */
  public displayCondition() {
    this.conditionButton = !this.conditionButton;
    if (!this.conditionButton && this._sharedService.conditionArray.length > 0) {
      this._sharedService.conditionArray = [];
      this._audienceService.setOptionToConditionFilter('false');
    }
  }

  /**
   * Search input
   */
  public displaySearchInput() {
    this.conditionInput = !this.conditionInput;
    setTimeout(() => {
      this.searchValue.nativeElement.focus();
    }, 100);
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
   * Delete saved tags
   * @param i {number}
   * @param data {object}
   */
  public async deleteSavedTag(i, data): Promise<void> {
    try {
      await this._userService.deleteTag(this._userService.userID, data[i].tagID);
      data.splice(i, 1);
      this._toastr.success('Tag gelöscht!');
    } catch (err) {
      this._sharedService.showRequestErrors(err);
    }
  }

  /**
   * Show all tags
   * @param key {string}
   */
  public showMoreTags(key) {
    this[key] = 1000;
  }

  /**
   * Choise saved condition
   * @param item {object}
   */
  public choiseSavedCondition(item) {
    this._sharedService.conditionArray = [];
    this._audienceService.searchFilterValue = '';
    this.searchFocusOut();
    if (item === 'All') {
      this.conditionButton = true;
    } else if (item === 'unsubscribe') {
      this.conditionButton = true;
      this._sharedService.conditionArray.push({
        name: 'status',
        criteria: 'is',
        value: 'unsubscribe'
      });
    } else if (item === 'subscribe') {
      this.conditionButton = true;
      this._sharedService.conditionArray.push({
        name: 'status',
        criteria: 'is',
        value: 'subscribe'
      });
    } else {
      this.conditionButton = true;
      if (item.tagID) {
        this._sharedService.conditionArray.push({
          name: 'Tag',
          type: 'tags',
          opened: false,
          criteria: 'is',
          value: item.name,
          tagID: item.tagID
        });
      } else if (item.widgetID) {
        this._sharedService.conditionArray.push({
          name: 'Opred-In Through',
          type: 'widgets',
          opened: false,
          criteria: 'is',
          value: item.name,
          widgetID: item.widgetID
        });
      } else if (item.sequenceID) {
        this._sharedService.conditionArray.push({
          name: 'Sequence subscription',
          type: 'sequences',
          opened: false,
          criteria: 'is',
          value: item.name,
          sequenceID: item.sequenceID
        });
      }
    }
    this._audienceService.setOptionToConditionFilter('false');
  }

  /**
   * Choise users status checked
   * @param data {array}
   */
  public choiseUsersCheck(data) {
    this.usersCheck = !this.usersCheck;

    data.forEach((item) => {
      item.selected = this.usersCheck;
      if (!item.selected) {
        this._audienceService.countCheckedUsers--;
        if (this._audienceService.countCheckedUsers < 0) {
          this._audienceService.countCheckedUsers = 0;
        }
      } else {
        this._audienceService.countCheckedUsers++;
        if (this._audienceService.countCheckedUsers > this._audienceService.dataUsers.length) {
          this._audienceService.countCheckedUsers = this._audienceService.dataUsers.length;
        }
      }
    });
    (this.usersCheck) ? this._audienceService.disabledBulkCheck = true : this._audienceService.disabledBulkCheck = false;
  }

  /**
   * Choise User item checked
   * @param user {object}
   * @param dataUsers {array}
   */
  public choiseUsersItemCheck(user, dataUsers) {
    let checkedStatusFalse = true;
    let checkedStatusTrue = true;
    user.selected = !user.selected;

    (user.selected) ? this._audienceService.countCheckedUsers++ : this._audienceService.countCheckedUsers--;

    dataUsers.forEach((data) => {
      if (data.selected === false) {
        checkedStatusFalse = false;
      }
      if (data.selected === true) {
        checkedStatusTrue = false;
      }
    });

    if (checkedStatusFalse) {
      this.usersCheck = true;
    }
    if (checkedStatusTrue) {
      this.usersCheck = false;
    }
    let counter = 0;
    this._audienceService.dataUsers.forEach((userItem) => {
      if (userItem.selected) {
        counter++;
      }
    });

    if (counter === 0) {
      this._audienceService.disabledBulkCheck = false;
    } else {
      this._audienceService.disabledBulkCheck = true;
    }
  }

  /**
   * Open popup
   * @param content {any}
   * @param id {number}
   */
  public openVerticallyCentered(content, id) {
    this.modalActiveClose = this._modalService.open(content, {'windowClass': 'width--840'});
    this.subscriberId = id;
  }

  /**
   * Set value to subscribers search input
   * @param value {string}
   */
  public getSubscribers(value) {
    this._audienceService.searchFilterValue = value;
    this._audienceService.getSubscribers();
  }

}
