import { Component, OnInit } from '@angular/core';
import { UserService } from '../../../../services/user.service';
import { SharedService } from '../../../../services/shared.service';
import { FbService } from '../../../../services/fb.service';
import { Router } from '@angular/router';

@Component({
  selector: 'app-invitation',
  templateUrl: './invitation.component.html',
  styleUrls: ['./invitation.component.scss']
})
export class InvitationComponent implements OnInit {

  constructor(
    private readonly _userService: UserService,
    private readonly _fbService: FbService,
    private readonly _router: Router,
    private readonly _sharedService: SharedService
  ) { }

  ngOnInit() {
    this.createPageAdminsByToken();
  }

  /**
   * Create page admins by token
   * @returns {Promise<void>}
   */
  public async createPageAdminsByToken(): Promise<any> {

    const token = window.location.pathname.split('/')[2];
    const tokens = localStorage.getItem('token');
    const page = localStorage.getItem('page');
    if (tokens && page) {
      try {
        const pageId = await this._userService.createPageAdminsByToken(token);
        localStorage.setItem('page', pageId.page_id);
        this._userService.userID = pageId.page_id;
        this._router.navigate(['/' + pageId.page_id + '/dashboard']);
      } catch (err) {
        this._router.navigate(['/invitation-not-correct']);
        this._sharedService.showRequestErrors(err);
      }
    } else {
      localStorage.setItem('invitationUrl', '/invitation/' + token);
      this._router.navigate(['/login']);
    }
  }

}
