import { Component, OnInit } from '@angular/core';
import { NgbModal } from '@ng-bootstrap/ng-bootstrap';
import { SharedService } from '../../../../../services/shared.service';
import { ToastrService } from 'ngx-toastr';

@Component({
  selector: 'app-user-settings-popup',
  templateUrl: './user-settings-popup.component.html',
  styleUrls: ['./user-settings-popup.component.scss']
})
export class UserSettingsPopupComponent implements OnInit {

  public myDetails = false;
  public modalActiveClose: any;
  public activeEmoji = false;
  public activeTab = 1;

  constructor(
    private toastr: ToastrService,
    private readonly _sharedService: SharedService,
    private readonly _modalService: NgbModal,
  ) { }

  ngOnInit() {

    setTimeout(() => {
      this._sharedService.preloaderView = false;
    }, 1000);
  }

  public settingTabs(number) {
    this.activeTab = number;
  }

  /**
   * Open popup
   * @param content {any}
   */
  public openVerticallyCenter(content) {
    this.modalActiveClose = this._modalService.open(content);
  }

  public displayEmoji(): void {
    this.activeEmoji = !this.activeEmoji;
  }

}
