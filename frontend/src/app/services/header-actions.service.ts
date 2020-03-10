import { Injectable } from '@angular/core';
import { BuilderService } from '../modules/messages-builder/services/builder.service';
import { UserService } from './user.service';
import { SharedService } from './shared.service';
import { ArrowsService } from '../modules/messages-builder/services/arrows.service';
import { Router } from '@angular/router';
import { NgbModal } from '@ng-bootstrap/ng-bootstrap';

@Injectable({
  providedIn: 'root'
})
export class HeaderActionsService {

  public modalActiveClose: any;
  public rout: any;
  // preloader
  // nameFlow

  constructor(
    private readonly _builderService: BuilderService,
    private readonly _userService: UserService,
    private readonly _router: Router,
    private readonly _modalService: NgbModal,
    private readonly _sharedService: SharedService,
    private readonly _arrowsService: ArrowsService,
  ) { }

  /**
   * Delete flow
   */
  public async deleteFlow(): Promise<any> {
    try {
      await this._userService.deleteFlow(this._userService.userID, this._sharedService.flowId);
      this.modalActiveClose.dismiss();
      this._router.navigate(['/' + this._userService.userID + this.rout + '/automation/menu/edit']);
    } catch (err) {
      this._sharedService.showRequestErrors(err);
    }
  }

  /**
   * Rename flow
   */
  public async renameFlow(value, nameFlow): Promise<any> {
    const data = {
      name: value
    };
    nameFlow = value;
    try {
      await this._userService.updateFlow(this._userService.userID, this._sharedService.flowId, data);
      this.modalActiveClose.dismiss();
    } catch (err) {
      this._sharedService.showRequestErrors(err);
    }
  }

  /**
   * Open popup
   * @param content
   * @param rout
   */
  public openVerticallyCentered(content, rout) {
    this.modalActiveClose = this._modalService.open(content);
    this.rout = rout;
  }
}
