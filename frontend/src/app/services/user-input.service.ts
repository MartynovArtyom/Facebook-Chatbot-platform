import { Injectable } from '@angular/core';
import { UserService } from './user.service';
declare let MessengerExtensions: any;

@Injectable({
  providedIn: 'root'
})
export class UserInputService {

  constructor(
    private _userService: UserService
  ) { }

  /**
   * Set user input
   * @param date {date}
   * @param queryParams {object}
   * @param {number} subscriberID {number}
   * @returns {Promise<any>}
   */
  public async selectUserInputDate(date, queryParams, subscriberID): Promise<any> {
    const data = {
      pageID: queryParams.pageID,
      subscriberID: subscriberID,
      flowID: queryParams.flowID,
      flowItemUuid: queryParams.flowItemUuid,
      itemUuid: queryParams.itemUuid,
      response: date
    };

    try {
      await this._userService.setUserInput(data);
      MessengerExtensions.requestCloseBrowser(function success() {
      }, function error(err) {
      });
    } catch (err) {
      console.log(err);
    }
  }
}
