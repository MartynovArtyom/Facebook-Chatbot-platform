import { Component, OnChanges, OnInit } from '@angular/core';
import { UserService } from '../../../services/user.service';
import { SharedService } from '../../../services/shared.service';

@Component({
  selector: 'app-update-plan',
  templateUrl: './update-plan.component.html',
  styleUrls: ['./update-plan.component.scss']
})
export class UpdatePlanComponent implements OnInit, OnChanges {

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
    this.sharedService.openUpdatePlan = false;
  }

}
