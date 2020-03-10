import { Component, OnInit } from '@angular/core';
import { SharedService } from '../../services/shared.service';
import { FbService } from '../../services/fb.service';
import { Router } from '@angular/router';

@Component({
  selector: 'app-user',
  templateUrl: './user.component.html',
  styleUrls: ['./user.component.scss']
})
export class UserComponent implements OnInit {
  constructor(
    public readonly sharedService: SharedService,
    private readonly _router: Router,
    private _fbService: FbService
  ) {
  }

  ngOnInit() {
    this.getUserInfo();
    this.sharedService.sidebarView = localStorage.getItem('sidebarPosition');
    this.sharedService.preloaderView = true;
    setTimeout(() => {
      this.onResize();
      this.sharedService.preloaderView = false;
    }, 1500);
  }

  public onResize() {
    if (window.innerWidth <= 900) {
      this.sharedService.sidebarView = 'mini';
    }
  }

  public async getUserInfo(): Promise<any> {
    try {
      const user = await this._fbService.getUserInfo();
      if (user.product && user.product.id) {
        localStorage.setItem('product', user.product.id);
        this.sharedService.userInfo = user;
      } else {
        this._router.navigate(['/not-product']);
        localStorage.removeItem('product');
      }
    } catch (err) {
      this.sharedService.showRequestErrors(err);
    }
  }

}
