import { Injectable } from '@angular/core';
import { CanActivate, ActivatedRouteSnapshot, RouterStateSnapshot, Router } from '@angular/router';
import { Observable } from 'rxjs';

@Injectable({
  providedIn: 'root'
})
export class CheckAddFbAccountGuard implements CanActivate {
  constructor(
    private _router: Router
  ) {}

  canActivate(
    next: ActivatedRouteSnapshot,
    state: RouterStateSnapshot): Observable<boolean> | Promise<boolean> | boolean {
    const token = localStorage.getItem('token');
    const href = window.location.pathname.split('/')[1];
    if (href === 'invitation' ||
        href === 'picker_date' ||
        href === 'picker_date_time' ||
        href === 'invitation-not-correct') {
      return true;
    } else {
      if (!token) {
        localStorage.setItem('previousUrl', state.url);
        this._router.navigate(['/login']);
      } else {
        return true;
      }
    }
  }
}
