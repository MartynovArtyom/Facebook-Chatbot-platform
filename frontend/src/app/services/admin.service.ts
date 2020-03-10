import { Injectable } from '@angular/core';
import { HttpClient, HttpParams } from '@angular/common/http';
import { SettingsService } from './settings.service';
import { AuthService } from 'angular4-social-login';
import { FbService } from './fb.service';
import { Router } from '@angular/router';

@Injectable({
  providedIn: 'root'
})
export class AdminService extends FbService {
  private _limit = '20';

  constructor(
    protected readonly _http: HttpClient,
    protected readonly _router: Router,
    protected readonly _authService: AuthService,
    protected readonly _settingsService: SettingsService
  ) {
    super(_http, _router, _authService, _settingsService);
  }

  /**
   * Get admin dashboard
   * @returns {Promise<Object>}
   */
  public async getDashboard(): Promise<any> {
    const headers = await this.createAuthorizationHeader();

    return this._http.get(this._settingsService.url + '/v2/admin/dashboard', { headers: headers['headers'] })
      .toPromise();
  }

  /**
   * Get admin users
   * @param data {object}
   * @returns {Promise<any>}
   */
  public async getUsers(data): Promise<any> {
    const headers = await this.createAuthorizationHeader();

    let params = new HttpParams();
    params = params.append('page', data.page);
    params = params.append('limit', this._limit);
    params = params.append('search', data.search);
    params = params.append('status', data.status);
    params = params.append('admin', data.admin);
    if (data.productId) {
      (data.productId === 'null') ? params = params.append('productId', null) :
        params = params.append('productId', data.productId);
    }
    if(data.createdFrom){
      const fromDate = data.createdFrom.getFullYear()+'-'+(data.createdFrom.getMonth()+1)+'-'+data.createdFrom.getDate();
      params = params.append('createdFrom', fromDate);
    }
    if(data.createdTo){
      const toDate = data.createdTo.getFullYear()+'-'+(data.createdTo.getMonth()+1)+'-'+data.createdTo.getDate();
      params = params.append('createdTo', toDate);
    }

    return this._http.get(this._settingsService.url + '/v2/admin/user/', { params: params, headers: headers['headers'] })
      .toPromise();
  }

  /**
   * Get admin pages
   * @param data {object}
   * @returns {Promise<any>}
   */
  public async getPages(data): Promise<any> {
    const headers = await this.createAuthorizationHeader();

    let params = new HttpParams();
    params = params.append('page', data.page);
    params = params.append('limit', this._limit);
    params = params.append('search', data.search);
    params = params.append('status', data.status);

    return this._http.get(this._settingsService.url + '/v2/admin/page/', { params: params, headers: headers['headers'] })
      .toPromise();
  }

  /**
   * Get admin products
   * @returns {Promise<any>}
   */
  public async getProducts(): Promise<any> {
    const headers = await this.createAuthorizationHeader();

    return this._http.get(this._settingsService.url + '/v2/admin/product/', { headers: headers['headers'] })
      .toPromise();
  }

  /**
   * Get admin user by id
   * @param id {number}
   * @returns {Promise<any>}
   */
  public async getUserById(id): Promise<any> {
    const headers = await this.createAuthorizationHeader();

    return this._http.get(this._settingsService.url + '/v2/admin/user/' + id, { headers: headers['headers'] })
      .toPromise();
  }

  /**
   * Get admin user token
   * @param id {number}
   * @returns {Promise<any>}
   */
  public async getUserToken(id): Promise<any> {
    const headers = await this.createAuthorizationHeader();

    return this._http.get(this._settingsService.url + '/v2/admin/user/' + id + '/token', { headers: headers['headers'] })
      .toPromise();
  }

  /**
   * Get admin page by id
   * @param id {number}
   * @returns {Promise<any>}
   */
  public async getPageById(id): Promise<any> {
    const headers = await this.createAuthorizationHeader();

    return this._http.get(this._settingsService.url + '/v2/admin/page/' + id, { headers: headers['headers'] })
      .toPromise();
  }

  /**
   * Update admin users
   * @param data {object}
   * @returns {Promise<any>}
   */
  public async createProduct(data): Promise<any> {
    const headers = await this.createAuthorizationHeader();

    return this._http.post(this._settingsService.url + '/v2/admin/product/', data, headers)
      .toPromise();
  }

  /**
   * Update admin users
   * @param data {object}
   * @param id {number}
   * @returns {Promise<any>}
   */
  public async updateUserProfile(data, id): Promise<any> {
    const headers = await this.createAuthorizationHeader();

    return this._http.patch(this._settingsService.url + '/v2/admin/user/' + id, data, headers)
      .toPromise();
  }

  /**
   * Edit admin users
   * @param data {object}
   * @param id {number}
   * @returns {Promise<any>}
   */
  public async editUserProfile(data, id): Promise<any> {
    const headers = await this.createAuthorizationHeader();

    return this._http.put(this._settingsService.url + '/v2/admin/user/' + id, data, headers)
      .toPromise();
  }

  /**
   * Update admin user page
   * @param data {object}
   * @param id {number}
   * @returns {Promise<any>}
   */
  public async updateUserPage(data, id): Promise<any> {
    const headers = await this.createAuthorizationHeader();

    return this._http.patch(this._settingsService.url + '/v2/admin/page/' + id, data, headers)
      .toPromise();
  }

  /**
   * Update admin user page
   * @param data {object}
   * @param id {number}
   * @returns {Promise<any>}
   */
  public async updateProduct(data, id): Promise<any> {
    const headers = await this.createAuthorizationHeader();

    return this._http.put(this._settingsService.url + '/v2/admin/product/' + id, data, headers)
      .toPromise();
  }

  /**
   * Delete tag
   * @param id {number}
   * @returns {Promise<Object>}
   */
  public async deleteUser(id): Promise<any> {
    const headers = await this.createAuthorizationHeader();

    return this._http.delete(this._settingsService.url + '/v2/admin/user/' + id, headers)
      .toPromise();
  }

  /**
   * Delete tag
   * @param id {number}
   * @returns {Promise<Object>}
   */
  public async deletePage(id): Promise<any> {
    const headers = await this.createAuthorizationHeader();

    return this._http.delete(this._settingsService.url + '/v2/admin/page/' + id, headers)
      .toPromise();
  }

  /**
   * Delete tag
   * @param id {number}
   * @returns {Promise<Object>}
   */
  public async deleteProduct(id): Promise<any> {
    const headers = await this.createAuthorizationHeader();

    return this._http.delete(this._settingsService.url + '/v2/admin/product/' + id, headers)
      .toPromise();
  }
}
