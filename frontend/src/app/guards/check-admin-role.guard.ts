import { Injectable } from '@angular/core';
import { CanActivate, ActivatedRouteSnapshot, RouterStateSnapshot, Router } from '@angular/router';
import { Observable } from 'rxjs';
import * as jwt_decode from 'jwt-decode';

@Injectable({
  providedIn: 'root'
})
export class CheckAdminRoleGuard implements CanActivate {
  constructor(
    private _router: Router
  ) {}
  canActivate(
    next: ActivatedRouteSnapshot,
    state: RouterStateSnapshot): Observable<boolean> | Promise<boolean> | boolean {
    const localId = localStorage.getItem('token');
    if (localId) {
      const res = this.getDecodedAccessToken(localId);
      if (res.roles === 'ROLE_ADMIN') {
        return true;
      } else {
        this._router.navigate(['/login']);
        return false;
      }
    } else {
      this._router.navigate(['/login']);
      return false;
    }
  }

  /**
   * decode JWT token
   * @param token {string}
   * @returns {null}
   */
  public getDecodedAccessToken(token: string): any {
    try {
      return jwt_decode(token);
    } catch (Error) {
      return null;
    }
  }
}
