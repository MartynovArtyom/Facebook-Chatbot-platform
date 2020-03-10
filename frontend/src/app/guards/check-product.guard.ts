import { Injectable } from '@angular/core';
import { CanActivate, ActivatedRouteSnapshot, RouterStateSnapshot, Router } from '@angular/router';
import { Observable } from 'rxjs';

@Injectable({
  providedIn: 'root'
})
export class CheckProductGuard implements CanActivate {
  constructor(
    private _router: Router
  ) {}
  canActivate(
    next: ActivatedRouteSnapshot,
    state: RouterStateSnapshot): Observable<boolean> | Promise<boolean> | boolean {
    const product = localStorage.getItem('product');
    if (!product) {
      localStorage.setItem('previousUrl', state.url);
      this._router.navigate(['/not-product']);
      return false;
    } else {
      return true;
    }
  }
}
