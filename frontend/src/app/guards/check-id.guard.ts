import { Injectable } from '@angular/core';
import { CanActivate, ActivatedRouteSnapshot, RouterStateSnapshot, Router, ActivatedRoute } from '@angular/router';
import { FbService } from '../services/fb.service';
import { LocationStrategy } from '@angular/common';
import { SharedService } from '../services/shared.service';
import { UserService } from '../services/user.service';

@Injectable({
  providedIn: 'root'
})
export class CheckIdGuard implements CanActivate {

  constructor(
    private readonly _fbService: FbService,
    private readonly _route: ActivatedRoute,
    private readonly _location: LocationStrategy,
    private readonly _sharedService: SharedService,
    private readonly _userService: UserService,
    private readonly _router: Router
  ) { }

  async canActivate(next: ActivatedRouteSnapshot, state: RouterStateSnapshot): Promise<boolean> {
    const localId = localStorage.getItem('token');
    const pageId = localStorage.getItem('page');
    const href = next.parent.url[0].path;
    const invitationUrl = localStorage.getItem('invitationUrl');
    const shareUrl = localStorage.getItem('shareUrl');
    const shareBot = localStorage.getItem('shareBot');
    const product = localStorage.getItem('product');
    const previousUrl = localStorage.getItem('previousUrl');

    if (localId && pageId) {
      if (previousUrl) {
        this._router.navigate([previousUrl]);
        localStorage.removeItem('previousUrl');
      } else if (invitationUrl) {
        this._router.navigate([invitationUrl]);
        localStorage.removeItem('invitationUrl');
      } else if (shareUrl) {
        this._router.navigate([shareUrl]);
        localStorage.removeItem('shareUrl');
      } else if (shareBot) {
        this._router.navigate([shareBot]);
        localStorage.removeItem('shareBot');
      } else {
        if (!product) {
          this._router.navigate(['/not-product']);
          return false;
        } else {
          if (!href) {
            return true;
          } else if (href === 'login' || href === 'add-fb-account') {
            return true;
          } else if (Number(pageId) === Number(href)) {
            return true;
          } else {
            const check = await this.checkPage(href);
            if (check === false) {
              this._location.replaceState('', 'ChatBo', '/\' + pageId + \'/dashboard', '');
              this._sharedService.preloaderView = true;
              setTimeout(() => {
                this._router.navigate(['/']);
                this._sharedService.preloaderView = false;
              }, 1000);
            }
            return true;
          }
        }
      }
    } else {
      localStorage.setItem('previousUrl', state.url);
      this._router.navigate(['/login']);
      return false;
    }
  }

  /**
   * Check Page
   */
  public async checkPage(href): Promise<any> {
    let result = false;
    try {
      const pages = await this._fbService.getAllConnectPage();
      pages.forEach(page => {
        if (Number(page.page_id) === Number(href)) {
          localStorage.setItem('page', page.page_id);
          this._userService.userID = page.page_id;
          result = true;
        }
      });
    } catch (err) {
      this._sharedService.showRequestErrors(err);
    }
    return result;
  }
}
