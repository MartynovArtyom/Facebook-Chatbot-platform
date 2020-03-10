import { Injectable } from '@angular/core';
import { CanActivate, ActivatedRouteSnapshot, RouterStateSnapshot, Router } from '@angular/router';
import { Observable } from 'rxjs';

@Injectable({
  providedIn: 'root'
})
export class CheckAuthIdGuard implements CanActivate {
  constructor(
    private _router: Router
  ) {}

  canActivate(
    next: ActivatedRouteSnapshot,
    state: RouterStateSnapshot): Observable<boolean> | Promise<boolean> | boolean {
    const token = localStorage.getItem('token');
    const id = localStorage.getItem('page');

    if (id && token) {
      this._router.navigate([id]);
      return true;
    } else if (!id && token) {
      this._router.navigate(['/add-fb-account']);
      return true;
    } else {
      return false;
    }
  }
}
