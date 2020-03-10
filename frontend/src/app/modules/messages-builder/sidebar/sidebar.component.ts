import {Component, Input, OnInit} from '@angular/core';
import { BuilderService } from '../services/builder.service';
import { Item } from '../builder/builder-interface';

@Component({
  selector: 'app-sidebar',
  templateUrl: './sidebar.component.html',
  styleUrls: ['./sidebar.component.scss']
})
export class SidebarComponent implements OnInit {

  @Input() config: Item;

  constructor(
    public readonly _builderService: BuilderService
  ) { }

  ngOnInit() {
  }

  public openBuilderSidebar() {
    this._builderService.openSidebar = !this._builderService.openSidebar;
    this._builderService.requestDataSidebar = {
      type: 'default',
      name: this._builderService.requestDataAll.name,
      widget_content: []
    };
  }

  public closeModal () {
    this._builderService.openSidebar = false;
  }

  public onClickedOutsideSidebar() {
    this._builderService.openSidebar = false;
  }

  public closeModalByOverlay (event: MouseEvent) {
    if (event.srcElement.getAttribute('id') === 'close-modal') {
      this._builderService.openSidebar = false;
    }
  }

}
