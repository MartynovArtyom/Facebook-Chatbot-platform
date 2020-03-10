import { Component, Input, OnInit } from '@angular/core';
import { Button } from '../../builder/builder-interface';
import { DragNDropComponent } from '../../drag-n-drop/drag-n-drop.component';
import { BuilderService } from '../../services/builder.service';

@Component({
  selector: 'app-buttons',
  templateUrl: './buttons.component.html',
  styleUrls: ['./buttons.component.scss']
})
export class ButtonsComponent extends DragNDropComponent implements OnInit {

  @Input() type: string;
  @Input() opened: any;
  @Input() buttons: Button[];

  constructor(
    public _builderService: BuilderService
  ) {
    super(_builderService);
  }

  ngOnInit() {
  }

}
