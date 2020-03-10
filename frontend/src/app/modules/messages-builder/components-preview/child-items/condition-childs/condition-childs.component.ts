import { Component, Input, OnInit } from '@angular/core';
import { DragNDropComponent } from '../../../drag-n-drop/drag-n-drop.component';
import { BuilderService } from '../../../services/builder.service';

@Component({
  selector: 'app-condition-childs',
  templateUrl: './condition-childs.component.html',
  styleUrls: ['./condition-childs.component.scss']
})
export class ConditionChildsComponent extends DragNDropComponent implements OnInit {

  @Input() config;

  constructor(
    public _builderService: BuilderService
  ) {
    super(_builderService);
  }

  ngOnInit() {
  }

}
