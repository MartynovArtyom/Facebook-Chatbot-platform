import { Component, OnInit } from '@angular/core';
import { NgbModal } from '@ng-bootstrap/ng-bootstrap';
import { SharedService } from '../../../../services/shared.service';
import { UserService } from '../../../../services/user.service';
import { ToastrService } from 'ngx-toastr';
import { AudienceService } from '../../../../services/audience.service';
import { Router } from '@angular/router';
import set = Reflect.set;

@Component({
  selector: 'app-user-autoposting',
  templateUrl: './user-autoposting.component.html',
  styleUrls: ['./user-autoposting.component.scss']
})
export class UserAutopostingComponent implements OnInit {

  public preloader = true;
  public disabledClick = false;
  public modalActiveClose: any;
  public autopostArray = [];
  public total: number;
  public currentPage = 1;
  public autopostObj = {
    name: 'RSS Feed',
    index: 1
  };

  constructor(
    private readonly _modalService: NgbModal,
    public readonly _userService: UserService,
    private readonly _sharedService: SharedService,
    private readonly _audienceService: AudienceService,
    private readonly _toastr: ToastrService,
  public readonly _router: Router
  ) { }

  ngOnInit() {
    this._audienceService.getSubscribersFilter().then(() => {
      this.getAllAutopostings();
    });
  }

  /**
   * Router to edit autoposting
   * @param id {number}
   */
  public routerToEdit(id) {
    if (!this.disabledClick) {
      this._router.navigate(['/' + this._userService.userID + '/autoposting/' + id]);
    }
  }

  /**
   * Change chanel button
   * @param name
   * @param index
   */
  public changeCanal(name, index) {
    this.autopostObj.name = name;
    this.autopostObj.index = index;
  }

  /**
   * Get all autopostings
   * @returns {Promise<void>}
   */
  public async getAllAutopostings(): Promise<any> {
    try {
      const data = await this._userService.getAllAutopostings(this._userService.userID, this.currentPage);
      data.items.forEach((item) => {
        this.autopostArray.push(item);
      });
      this.total = data.pagination.total_count;
      this.preloader = false;
      this._sharedService.preloaderView = false;
    } catch (err) {
      this._sharedService.showRequestErrors(err);
    }
  }

  /**
   * Create autoposting
   * @returns {Promise<void>}
   */
  public async createAutoposting(value): Promise<any> {
    const data = {
      type: this.autopostObj.index,
      url: value
    };
    try {
      const response = await this._userService.createAutoposting(this._userService.userID, data);
      this.autopostArray.push(response);
      this._toastr.success('Kanal hinzugefügt');
      this.modalActiveClose.dismiss();
    } catch (err) {
      this._sharedService.showRequestErrors(err);
    }
  }

  /**
   * Delete autoposting
   * @param id {number}
   * @param index {number}
   * @returns {Promise<void>}
   */
  public async deleteAutoposting(id, index): Promise<any> {
    this.disabledClick = true;
    try {
      await this._userService.deleteAutoposting(this._userService.userID, id);
      this.total--;
      this.autopostArray.splice(index, 1);
      this.disabledClick = false;
      this._toastr.success('Kanal entfernt');
    } catch (err) {
      this._sharedService.showRequestErrors(err);
    }
  }

  /**
   * Open popup
   * @param content
   */
  public openVerticallyCentered(content) {
    this.modalActiveClose = this._modalService.open(content, { 'size': 'lg' });
  }

}
