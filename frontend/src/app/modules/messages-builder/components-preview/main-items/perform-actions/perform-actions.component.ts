import { Component, Input, OnInit } from '@angular/core';
import { Item } from '../../../builder/builder-interface';
import { BuilderFunctionsService } from '../../../services/builder-functions.service';
import { BuilderService } from '../../../services/builder.service';
import { DragNDropComponent } from '../../../drag-n-drop/drag-n-drop.component';
import { ArrowsService } from '../../../services/arrows.service';

@Component({
  selector: 'app-perform-actions',
  templateUrl: './perform-actions.component.html',
  styleUrls: ['./perform-actions.component.scss', '../../../assets/general-style.scss']
})
export class PerformActionsComponent extends DragNDropComponent implements OnInit {

  @Input() config: Item;

  constructor(
    public readonly _builderService: BuilderService,
    public readonly _builderFunctionsService: BuilderFunctionsService,
    public readonly _arrowsService: ArrowsService
  ) {
    super(_builderService);
  }

  ngOnInit() {
    super.ngOnInit();
  }

}
