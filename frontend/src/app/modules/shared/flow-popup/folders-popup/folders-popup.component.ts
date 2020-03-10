import { Component, Input, OnInit } from '@angular/core';
import { FolderItemType } from '../../../../../entities/models-user';
import { FlowFoldersService } from '../../../../services/flow-folders.service';
import { UserService } from '../../../../services/user.service';

@Component({
  selector: 'app-folders-popup',
  templateUrl: './folders-popup.component.html',
  styleUrls: ['./folders-popup.component.scss']
})
export class FoldersPopupComponent implements OnInit {
  public _flowFolders = Array<FolderItemType>();
  @Input('flowFolders') set flowFolders(flowFolders) {
    if (flowFolders) {
      this._flowFolders = flowFolders;
    }
  }
  get sendPopup() {
    return this._flowFolders;
  }

  constructor(
    public readonly _flowFoldersService: FlowFoldersService,
    public readonly _userService: UserService,
  ) {
  }

  ngOnInit() {
  }

  /**
   * Opening folders
   * @param folder {object}
   * @param status {boolean}
   */
  public actionOpeningFolder(folder, status) {
    if (status) {
      folder['status'] = true;
    } else {
      delete folder['status'];
    }
  }

}
