import { Component, Input, OnChanges, OnInit } from '@angular/core';
import { SharedService } from '../../../services/shared.service';
import { UserService } from '../../../services/user.service';

@Component({
  selector: 'app-open-messenger-toastr',
  templateUrl: './open-messenger-toastr.component.html',
  styleUrls: ['./open-messenger-toastr.component.scss']
})
export class OpenMessengerToastrComponent implements OnInit, OnChanges {

  constructor(
    public sharedService: SharedService,
    public userService: UserService
  ) { }

  ngOnInit() {
    setTimeout(() => {
      this.closePopup();
    }, 5000);
  }

  ngOnChanges() {
    setTimeout(() => {
      this.closePopup();
    }, 5000);
  }

  public closePopup() {
    this.sharedService.openMessage = false;
  }

}
