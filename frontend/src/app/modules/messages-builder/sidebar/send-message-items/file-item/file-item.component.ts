import { Component, Input, OnInit } from '@angular/core';
import { Child } from '../../../builder/builder-interface';
import { BuilderFunctionsService } from '../../../services/builder-functions.service';
import { BuilderService } from '../../../services/builder.service';

@Component({
  selector: 'app-file-item',
  templateUrl: './file-item.component.html',
  styleUrls: [
    './file-item.component.scss',
    '../../../assets/general-style.scss'
  ]
})
export class FileItemComponent implements OnInit {

  @Input() item: Child;
  @Input() opened: number;

  constructor(
    public readonly _builderFunctionsService: BuilderFunctionsService,
    public readonly _builderService: BuilderService
  ) { }

  ngOnInit() {
  }

}
