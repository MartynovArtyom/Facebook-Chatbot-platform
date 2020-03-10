import { Component, Input, OnInit } from '@angular/core';
import { Child } from '../../../../builder/builder-interface';
import { BuilderService } from '../../../../services/builder.service';

@Component({
  selector: 'app-user-input-child',
  templateUrl: './user-input-child.component.html',
  styleUrls: ['./user-input-child.component.scss']
})
export class UserInputChildComponent implements OnInit {

  @Input() config: Child;
  @Input() opened: any;

  constructor(
    public _builderService: BuilderService
  ) {

  }

  ngOnInit() {
  }

}
