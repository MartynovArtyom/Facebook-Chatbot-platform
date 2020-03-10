import { Component, Input, OnInit } from '@angular/core';
import { Child } from '../../../../builder/builder-interface';

@Component({
  selector: 'app-audio-child',
  templateUrl: './audio-child.component.html',
  styleUrls: ['./audio-child.component.scss']
})
export class AudioChildComponent implements OnInit {

  @Input() config: Child;

  ngOnInit() {
  }

}
