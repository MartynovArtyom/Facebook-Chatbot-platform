import { Component, Input, OnInit } from '@angular/core';
import { DragNDropComponent } from '../../../drag-n-drop/drag-n-drop.component';
import { BuilderService } from '../../../services/builder.service';

@Component({
  selector: 'app-perform-actions-childs',
  templateUrl: './perform-actions-childs.component.html',
  styleUrls: ['./perform-actions-childs.component.scss']
})
export class PerformActionsChildsComponent extends DragNDropComponent implements OnInit {

  @Input() config;

  constructor(
    public _builderService: BuilderService
  ) {
    super(_builderService);
  }

  ngOnInit() {
  }

}
