import { Component, OnInit } from '@angular/core';
import { BuilderService } from '../../services/builder.service';

@Component({
  selector: 'app-default-items',
  templateUrl: './default-items.component.html',
  styleUrls: ['./default-items.component.scss']
})
export class DefaultItemsComponent implements OnInit {

  constructor(
    public _builderService: BuilderService
  ) { }

  ngOnInit() {
  }

}
