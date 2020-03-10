import { Component, OnInit } from '@angular/core';
import { SharedService } from '../../../../services/shared.service';
import { FbService } from '../../../../services/fb.service';
import { Router } from '@angular/router';

@Component({
  selector: 'app-not-product',
  templateUrl: './not-product.component.html',
  styleUrls: ['./not-product.component.scss']
})
export class NotProductComponent implements OnInit {
  public link = '';

  constructor(
    public sharedService: SharedService,
    private _fbService: FbService,
    private _router: Router
  ) { }

  ngOnInit() {

    const free = localStorage.getItem('freeLink');
    (free === 'free') ? this.link = 'https://www.digistore24.com/product/259961' : this.link = 'https://chatbo.de/bestellen/';

    this.sharedService.preloaderView = true;
    const product = localStorage.getItem('product');
    if (product) {
      this._router.navigate(['/login']);
    } else {
      this.getUserInfo();
    }
    this.sharedService.preloaderView = true;
  }

  public async getUserInfo(): Promise<any> {
    try {
      const user = await this._fbService.getUserInfo();
      if (user.product && user.product.id) {
        localStorage.setItem('product', user.product.id);
        this.sharedService.userInfo = user;
        this._router.navigate(['/login']);
      } else {
        window.location.href = 'https://chatbo.de/upgrade/';
      }
    } catch (err) {
      this.sharedService.showRequestErrors(err);
    }
  }

  public logOut() {
    this._fbService.logout();
  }

}
