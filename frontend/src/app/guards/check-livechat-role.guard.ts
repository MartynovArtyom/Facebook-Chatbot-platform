import { Injectable } from '@angular/core';
import { CanActivate, ActivatedRouteSnapshot, RouterStateSnapshot, Router } from '@angular/router';
import { Observable } from 'rxjs';
import { UserService } from '../services/user.service';

@Injectable({
  providedIn: 'root'
})
export class CheckLivechatRoleGuard implements CanActivate {
  constructor(
    private _userService: UserService,
    private _router: Router
  ) {}
  canActivate(
    next: ActivatedRouteSnapshot,
    state: RouterStateSnapshot): Observable<boolean> | Promise<boolean> | boolean {
    const role = localStorage.getItem('role');
    if (Number(role) === 3) {
      this._router.navigate(['/login']);
      return false;
    } else {
      return true;
    }
  }
}
