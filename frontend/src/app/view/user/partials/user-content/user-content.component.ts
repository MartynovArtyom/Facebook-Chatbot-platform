import { Component, OnInit } from '@angular/core';
import { SharedService } from '../../../../services/shared.service';
import { Router } from '@angular/router';
import { UserService } from '../../../../services/user.service';
import { NgbModal } from '@ng-bootstrap/ng-bootstrap';

@Component({
  selector: 'app-user-content',
  templateUrl: './user-content.component.html',
  styleUrls: ['./user-content.component.scss']
})
export class UserContentComponent implements OnInit {

  public preloader = true;

  constructor(
    private readonly _modalService: NgbModal,
    public readonly _userService: UserService,
    public readonly _router: Router,
    private readonly _sharedService: SharedService
  ) { }

  ngOnInit() {
    setTimeout(() => {
      this.preloader = false;
    }, 1000);
  }

}
