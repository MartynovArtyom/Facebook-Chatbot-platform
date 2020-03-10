import { Injectable } from '@angular/core';
import {CanActivate, ActivatedRouteSnapshot, RouterStateSnapshot, Router} from '@angular/router';
import { Observable } from 'rxjs';

@Injectable({
  providedIn: 'root'
})
export class CheckAuthLoginGuard implements CanActivate {
  constructor(

    private readonly _router: Router
  ) {}

  canActivate(
    next: ActivatedRouteSnapshot,
    state: RouterStateSnapshot): Observable<boolean> | Promise<boolean> | boolean {
    const pageId = localStorage.getItem('page');
    const token = localStorage.getItem('token');
    const invitationUrl = localStorage.getItem('invitationUrl');
    const shareUrl = localStorage.getItem('shareUrl');
    const shareBot = localStorage.getItem('shareBot');
    const previousUrl = localStorage.getItem('previousUrl');
    if (token && pageId) {
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
        this._router.navigate(['/' + pageId + '/dashboard']);
      }
      return false;
    } else if (token && !pageId) {
      this._router.navigate(['/add-fb-account']);
    }
    return true;
  }
}
