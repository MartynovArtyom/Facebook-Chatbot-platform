import { Component, OnInit } from '@angular/core';
import { FbService } from '../../../../services/fb.service';
import { AuthService, FacebookLoginProvider } from 'angular4-social-login';
import { SharedService } from '../../../../services/shared.service';
import { Router } from '@angular/router';
import { CookieService } from 'ngx-cookie-service';
import * as jwt_decode from 'jwt-decode';

@Component({
  selector: 'app-login-standart',
  templateUrl: './login-standart.component.html',
  styleUrls: ['./login-standart.component.scss']
})
export class LoginStandartComponent implements OnInit {

  public date = new Date();
  public checkCondition = true;

  constructor(
    public readonly _fbService: FbService,
    private authService: AuthService,
    private _sharedService: SharedService,
    private _cookieService: CookieService,
    private readonly _router: Router
  ) { }

  ngOnInit() {
    this._fbService.loginButtonLoader = false;
  }

  public changeCheckCondition(){
    this.checkCondition = !this.checkCondition;
  }

  /**
   * Login
   */
  public loginWithFacebook(): void {
    this._fbService.loginButtonLoader = true;
    this.authService.signIn(FacebookLoginProvider.PROVIDER_ID).then(data => {
      this._fbService.getToken(data.authToken, true).then(response => {
        localStorage.setItem('token', response.token);
        localStorage.setItem('freeLink', 'free');
        const res = this.getDecodedAccessToken(response.token);
        if (res.username) {
          localStorage.setItem('useremail', res.username);
        }

        this._fbService.getUserInfo().then(user => {
          const firstLogin = this._cookieService.get('firstLogin');
          if (user.product && user.product.id) {
            localStorage.setItem('product', user.product.id);
          }

          this._fbService.getAllConnectPage().then(resp => {
            if (!resp || resp['length'] === 0) {
              this._router.navigate(['/add-fb-account']);
              this._fbService.loginButtonLoader = false;
            } else {
              localStorage.setItem('page', resp[0].page_id);
              this._fbService.loginButtonLoader = false;
              if (!firstLogin || firstLogin !== 'true') {
                this._cookieService.set('firstLogin', 'true');
                this._router.navigate(['/add-fb-account']);
              } else {
                this._router.navigate([resp[0].page_id]);
              }
            }
          }).catch((err) => {
            this._fbService.loginButtonLoader = false;
            this._sharedService.showRequestErrors(err);
          });

        }).catch((err) => {
          this._fbService.loginButtonLoader = false;
          this._sharedService.showRequestErrors(err);
        });
      }).catch((err) => {
        this._fbService.loginButtonLoader = false;
        this._sharedService.showRequestErrors(err);
      });
    }).catch((err) => {
      this._fbService.loginButtonLoader = false;
      this._sharedService.showRequestErrors(err);
    });
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
