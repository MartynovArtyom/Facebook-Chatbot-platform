import { Component, OnInit } from '@angular/core';
import { Router } from '@angular/router';
import { SharedService } from '../../../../services/shared.service';
import { FbService } from '../../../../services/fb.service';

@Component({
  selector: 'app-not-correct-invitation',
  templateUrl: './not-correct-invitation.component.html',
  styleUrls: ['./not-correct-invitation.component.scss']
})
export class NotCorrectInvitationComponent implements OnInit {
  public versionCheck = false;

  constructor(
    private readonly _router: Router,
    private readonly _fbService: FbService,
    public readonly sharedService: SharedService,
  ) { }

  ngOnInit() {
    this.sharedService.preloaderView = true;
    this.getUserInfo();
  }

  public async getUserInfo(): Promise<any> {
    try {
      const user = await this._fbService.getUserInfo();
      if (user.product && user.product.id && user.product.admins) {
        localStorage.setItem('product', user.product.id);
        this.sharedService.userInfo = user;
        this.sharedService.preloaderView = false;
        this.versionCheck = false;
      } else {
        this.sharedService.preloaderView = false;
        this.versionCheck = true;
      }
    } catch (err) {
      this.sharedService.showRequestErrors(err);
    }
  }

  /**
   * Back to login
   */
  public backToLogin() {
    location.reload();
    setTimeout(() => {
      this._router.navigate(['/']);
    }, 10);
  }

}
